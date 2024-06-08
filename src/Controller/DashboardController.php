<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'home')]
    public function index(ProductRepository $productRepository, OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {
            // Get product count
            $productCount = count($productRepository->findAll());

            // Get order count
            $orderCount = count($orderRepository->findAll());

            return $this->render('dashboard/index.html.twig', [
                'productCount' => $productCount,
                'orderCount' => $orderCount,

            ]);
        }

        return $this->render('dashboard/home.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
}

