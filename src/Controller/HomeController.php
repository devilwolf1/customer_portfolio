<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CustomerRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CustomerRepository $customerRepository,
        private CategoryRepository $categoryRepository,
    ) {}

    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        // Si non authentifié, rediriger vers login
        if (!$this->getUser()) {
            return $this->redirectToRoute('auth_login');
        }
        
        $stats = [
            'total_products' => $this->productRepository->getTotalCount(),
            'total_customers' => $this->customerRepository->getTotalCount(),
            'total_categories' => count($this->categoryRepository->findAllSorted()),
            'recent_products' => $this->productRepository->findPaginated(1, 5),
        ];

        return $this->render('home/index.html.twig', $stats);
    }
}
