<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerFormType;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;

#[Route('/customers', name: 'customer_')]
class CustomerController extends AbstractController
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {}

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 10;

        $total = $this->customerRepository->getTotalCount();
        $customers = $this->customerRepository->findPaginated($page, $limit);
        $pages = ceil($total / $limit);

        return $this->render('customer/index.html.twig', [
            'customers' => $customers,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        try {
            $customer = new Customer();
            $form = $this->createForm(CustomerFormType::class, $customer);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->persist($customer);
                $this->entityManager->flush();

                $this->logger->info('Customer created successfully', [
                    'customer_id' => $customer->getId(),
                    'customer_name' => $customer->getName(),
                    'customer_email' => $customer->getEmail()
                ]);

                $this->addFlash('success', 'Client créé avec succès !');

                return $this->redirectToRoute('customer_list');
            }

            return $this->render('customer/create.html.twig', [
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la création du client.');
            $this->logger->error('Error creating customer: ' . $e->getMessage(), [
                'exception' => $e,
                'customer_data' => $customer ? $customer->getName() : 'N/A'
            ]);
            return $this->redirectToRoute('customer_create');
        }
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Customer $customer, Request $request): Response
    {
        $form = $this->createForm(CustomerFormType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->logger->info('Customer updated successfully', [
                'customer_id' => $customer->getId(),
                'customer_name' => $customer->getName(),
                'customer_email' => $customer->getEmail()
            ]);

            $this->addFlash('success', 'Client modifié avec succès !');

            return $this->redirectToRoute('customer_list');
        }

        return $this->render('customer/edit.html.twig', [
            'form' => $form,
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Customer $customer, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $customer->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($customer);
            $this->entityManager->flush();

            $this->addFlash('success', 'Client supprimé avec succès !');
        }

        return $this->redirectToRoute('customer_list');
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Customer $customer): Response
    {
        return $this->render('customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }
}
