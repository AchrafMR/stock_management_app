<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class UserProductController extends AbstractController
{
    #[Route('/user/product', name: 'app_user_product')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $categoryId = $request->query->get('category');
        $categories = $categoryRepository->findAll();
        $products = $categoryId ? $productRepository->findBy(['category' => $categoryId]) : $productRepository->findAll();

        return $this->render('user/user_product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'current_category' => $categoryId,
        ]);
    }

    #[Route('/user/category/{id}/product', name: 'productsByCategory')]
    public function productsByCategory(int $id, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        $products = $productRepository->findBy(['category' => $category]);

        return $this->render('user/user_product/products_by_category.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    #[Route('/user/cart/add/{id}', name: 'app_add_to_cart')]
    public function addToCart(int $id, ProductRepository $productRepository, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (!isset($cart[$id])) {
            $cart[$id] = 1;
        } else {
            $cart[$id]++;
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_user_product');
    }

    #[Route('/cart', name: 'app_cart')]
    public function cart(ProductRepository $productRepository, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $products = [];

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);

            if ($product) {
                $product->quantity = $quantity;
                $products[] = $product;
            }
        }

        return $this->render('user/user_product/cart.html.twig', [
            'products' => $products,
        ]);
    }
    #[Route('/cart/remove/{id}', name: 'app_remove_from_cart')]
    public function removeFromCart(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
    
        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
            $this->addFlash('success', 'Product removed successfully.');
        }
    
        return $this->redirectToRoute('app_cart'); // Redirect back to the cart page
    }
    

}
