<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Form\OrderFormType;
use App\Repository\OrderRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;

#[Route('/orders', name: 'order_')]
class OrderController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private CustomerRepository $customerRepository,
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $orders = $this->orderRepository->findAllSorted();

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        try {
            $order = new Order();
            $form = $this->createForm(OrderFormType::class, $order);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Générer un numéro de commande unique
                $order->setOrderNumber('ORD-' . uniqid() . '-' . date('YmdHis'));

                // Calculer le total des items
                $total = 0;
                foreach ($order->getOrderItems() as $item) {
                    $item->calculateLineTotal();
                    $total += (float) $item->getLineTotal();
                }
                $order->setTotal((string) $total);

                $this->entityManager->persist($order);
                $this->entityManager->flush();

                $this->logger->info('Order created successfully', [
                    'order_id' => $order->getId(),
                    'order_number' => $order->getOrderNumber(),
                    'customer' => $order->getCustomer() ? $order->getCustomer()->getName() : 'N/A',
                    'total' => $order->getTotal()
                ]);

                $this->addFlash('success', 'Commande créée avec succès !');

                return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
            }

            return $this->render('order/create.html.twig', [
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la création de la commande.');
            $this->logger->error('Error creating order: ' . $e->getMessage(), [
                'exception' => $e,
                'order_data' => $order ? $order->getOrderNumber() : 'N/A'
            ]);
            return $this->redirectToRoute('order_create');
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Order $order, Request $request): Response
    {
        $form = $this->createForm(OrderFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Recalculer le total
            $total = 0;
            foreach ($order->getOrderItems() as $item) {
                $item->calculateLineTotal();
                $total += (float) $item->getLineTotal();
            }
            $order->setTotal((string) $total);
            $order->setUpdatedAt(new \DateTime());

            $this->entityManager->flush();

            $this->logger->info('Order updated successfully', [
                'order_id' => $order->getId(),
                'order_number' => $order->getOrderNumber(),
                'customer' => $order->getCustomer() ? $order->getCustomer()->getName() : 'N/A',
                'total' => $order->getTotal()
            ]);

            $this->addFlash('success', 'Commande modifiée avec succès !');

            return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
        }

        return $this->render('order/edit.html.twig', [
            'form' => $form,
            'order' => $order,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Order $order, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($order);
            $this->entityManager->flush();

            $this->logger->info('Order deleted successfully', [
                'order_id' => $order->getId(),
                'order_number' => $order->getOrderNumber()
            ]);

            $this->addFlash('success', 'Commande supprimée avec succès !');
        }

        return $this->redirectToRoute('order_index');
    }

    #[Route('/{id}/status/{status}', name: 'update_status', methods: ['POST'])]
    public function updateStatus(Order $order, string $status, Request $request): Response
    {
        $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

        if (in_array($status, $validStatuses) && $this->isCsrfTokenValid('status' . $order->getId(), $request->request->get('_token'))) {
            $order->setStatus($status);
            $order->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();

            $this->logger->info('Order status updated successfully', [
                'order_id' => $order->getId(),
                'order_number' => $order->getOrderNumber(),
                'old_status' => $order->getStatus(),
                'new_status' => $status
            ]);

            $this->addFlash('success', 'Statut de la commande mis à jour !');
        }

        return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
    }
}
