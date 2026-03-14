<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/customers', name: 'api_customers_')]
#[IsGranted('ROLE_USER')]
class CustomerApiController extends AbstractController
{
    public function __construct(private CustomerRepository $customerRepository)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $customers = $this->customerRepository->findAll();

        $data = array_map(function (Customer $customer) {
            return $this->serializeCustomer($customer);
        }, $customers);

        return $this->json([
            'status' => 'success',
            'count' => count($data),
            'data' => $data,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Customer $customer): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data' => $this->serializeCustomer($customer),
        ]);
    }

    private function serializeCustomer(Customer $customer): array
    {
        return [
            'id' => $customer->getId(),
            'customer_id' => $customer->getCustomerId(),
            'name' => $customer->getCustomerName(),
            'segment' => $customer->getSegment(),
            'country' => $customer->getCountry(),
            'city' => $customer->getCity(),
            'state' => $customer->getState(),
            'region' => $customer->getRegion(),
            'postal_code' => $customer->getPostalCode(),
            'orders_count' => $customer->getOrders()->count(),
        ];
    }
}
