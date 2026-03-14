<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/products', name: 'api_products_')]
#[IsGranted('ROLE_USER')]
class ProductApiController extends AbstractController
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $category = $request->query->get('category');
        
        if ($category) {
            $products = $this->productRepository->findByCategory($category);
        } else {
            $products = $this->productRepository->findAll();
        }

        $data = array_map(function (Product $product) {
            return $this->serializeProduct($product);
        }, $products);

        return $this->json([
            'status' => 'success',
            'count' => count($data),
            'data' => $data,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data' => $this->serializeProduct($product),
        ]);
    }

    private function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => (float) $product->getPrice(),
            'quantity' => $product->getQuantity(),
            'category' => $product->getCategory()?->getName(),
            'created_at' => $product->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $product->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
