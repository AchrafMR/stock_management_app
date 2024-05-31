<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserProductController extends AbstractController
{
    #[Route('/user/product', name: 'app_user_product')]
    public function index(ProductRepository $productRepository): Response
    {
        $products=$productRepository->findAll();        
        return $this->render('user/user_product/index.html.twig', [
            'controller_name' => 'UserProductController',
            'products'=>$products,
        ]);
    }

    #[Route('/user/category/{id}/product', name: 'productsByCategory')]
    public function productsByCategory(int $id, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        // Fetch the Category entity using the ID
        $category = $categoryRepository->find($id);

        // Check if the category exists
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        // Fetch products associated with the category
        $products = $productRepository->findBy(['category' => $category]);

        return $this->render('user/user_product/products_by_category.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
