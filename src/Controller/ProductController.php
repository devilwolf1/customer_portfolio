<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

#[Route('/products', name: 'product_')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {}

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $categoryId = $request->query->get('category');
        $limit = 10;

        if ($categoryId) {
            $products = $this->productRepository->findByCategory($categoryId, $page, $limit);
            $total = $this->productRepository->getCategoryCount($categoryId);
        } else {
            $products = $this->productRepository->findPaginated($page, $limit);
            $total = $this->productRepository->getTotalCount();
        }

        $totalPages = ceil($total / $limit);
        $categories = $this->categoryRepository->findAllSorted();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'selectedCategory' => $categoryId,
            'total' => $total,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        try {
            $product = new Product();
            $form = $this->createForm(ProductFormType::class, $product);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Additional server-side validation
                $errors = $this->validator->validate($product);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                    return $this->render('product/create.html.twig', [
                        'form' => $form,
                    ]);
                }

                if (!$product->getCreatedAt()) {
                    $product->setCreatedAt(new \DateTime());
                }
                $product->setUpdatedAt(new \DateTime());
                $this->entityManager->persist($product);
                $this->entityManager->flush();

                $this->logger->info('Product created successfully', [
                    'product_id' => $product->getId(),
                    'product_name' => $product->getName(),
                    'category' => $product->getCategory() ? $product->getCategory()->getName() : 'N/A'
                ]);

                $this->addFlash('success', 'Produit créé avec succès !');

                return $this->redirectToRoute('product_list');
            }

            return $this->render('product/create.html.twig', [
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la création du produit.');
            // Log the error
            $this->logger->error('Error creating product: ' . $e->getMessage(), [
                'exception' => $e,
                'product_data' => $product ? $product->getName() : 'N/A'
            ]);
            return $this->redirectToRoute('product_create');
        }
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Product $product, Request $request): Response
    {
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès !');

            return $this->redirectToRoute('product_list');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form,
            'product' => $product,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Product $product, Request $request): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
                $this->entityManager->remove($product);
                $this->entityManager->flush();

                $this->logger->info('Product deleted successfully', [
                    'product_id' => $product->getId(),
                    'product_name' => $product->getName()
                ]);

                $this->addFlash('success', 'Produit supprimé avec succès !');
            }

            return $this->redirectToRoute('product_list');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du produit.');
            $this->logger->error('Error deleting product: ' . $e->getMessage(), [
                'exception' => $e,
                'product_id' => $product->getId(),
                'product_name' => $product->getName()
            ]);
            return $this->redirectToRoute('product_list');
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
