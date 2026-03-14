<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/orders', name: 'api_orders_')]
#[IsGranted('ROLE_USER')]
class OrderApiController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $orders = $this->orderRepository->findAllSorted();

        $data = array_map(function (Order $order) {
            return $this->serializeOrder($order);
        }, $orders);

        return $this->json([
            'status' => 'success',
            'count' => count($data),
            'data' => $data,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Order $order): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data' => $this->serializeOrder($order),
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['customer_id'], $data['status'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Missing required fields: customer_id, status',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Find the customer by ID
        $customer = $this->entityManager->getRepository(\App\Entity\Customer::class)->find($data['customer_id']);
        if (!$customer) {
            return $this->json([
                'status' => 'error',
                'message' => 'Customer not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $order = new Order();
        $order->setOrderNumber('ORD-' . uniqid() . '-' . date('YmdHis'));
        $order->setCustomer($customer);
        $order->setStatus($data['status']);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Order created',
            'data' => $this->serializeOrder($order),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(Order $order, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['status'])) {
            $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($data['status'], $validStatuses)) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid status',
                ], Response::HTTP_BAD_REQUEST);
            }
            $order->setStatus($data['status']);
        }

        $order->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Order updated',
            'data' => $this->serializeOrder($order),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Order $order): JsonResponse
    {
        $this->entityManager->remove($order);
        $this->entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Order deleted',
        ]);
    }

    /**
     * Sérialise une commande en array JSON
     */
    private function serializeOrder(Order $order): array
    {
        $items = array_map(function ($item) {
            return [
                'id' => $item->getId(),
                'product' => $item->getProduct()->getName(),
                'unit_price' => (float) $item->getUnitPrice(),
                'quantity' => $item->getQuantity(),
                'line_total' => (float) $item->getLineTotal(),
            ];
        }, $order->getOrderItems()->toArray());

        return [
            'id' => $order->getId(),
            'order_number' => $order->getOrderNumber(),
            'customer' => [
                'id' => $order->getCustomer()->getId(),
                'name' => $order->getCustomer()->getCustomerName(),
            ],
            'total' => (float) $order->getTotal(),
            'status' => $order->getStatus(),
            'items' => $items,
            'created_at' => $order->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $order->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
