<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'home')]
    public function index(ProductRepository $productRepository, OrderRepository $orderRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, LoggerInterface $logger): Response
    {
        $user = $this->getUser();
        if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {

            try {
                // Get data for orders per month chart using QueryBuilder
                $queryBuilder = $entityManager->createQueryBuilder();
                $queryBuilder->select("SUBSTRING(o.date, 1, 7) as month, COUNT(o.id) as order_count")
                    ->from('App\Entity\Order', 'o')
                    ->groupBy('month')
                    ->orderBy('month', 'ASC');

                $result = $queryBuilder->getQuery()->getResult();

                $logger->info('Order Data:', ['result' => $result]); // Log the result to debug

                $chartData = [
                    'months' => array_column($result, 'month'),
                    'order_counts' => array_column($result, 'order_count')
                ];

                $logger->info('Chart Data:', ['chartData' => $chartData]); // Log the chart data

                return $this->render('dashboard/index.html.twig', [
                    'chartData' => $serializer->serialize($chartData, 'json')
                ]);
            } catch (\Exception $e) {
                $logger->error('Error generating chart data: ' . $e->getMessage());
                return new Response('An error occurred while generating the chart data.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->render('dashboard/home.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
}
// namespace App\Controller;

// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\Annotation\Route;
// use App\Repository\ProductRepository;
// use App\Repository\OrderRepository;
// use Symfony\Component\Serializer\SerializerInterface;
// use Doctrine\ORM\EntityManagerInterface;
// use Psr\Log\LoggerInterface;

// class DashboardController extends AbstractController
// {
//     #[Route('/dashboard', name: 'home')]
//     public function index(ProductRepository $productRepository, OrderRepository $orderRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, LoggerInterface $logger): Response
//     {
//         $user = $this->getUser();
//         if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {

//             try {
//                 // Get data for orders per month chart using QueryBuilder
//                 $queryBuilder = $entityManager->createQueryBuilder();
//                 $queryBuilder->select("SUBSTRING(o.date, 1, 7) as month, COUNT(o.id) as order_count")
//                     ->from('App\Entity\Order', 'o')
//                     ->groupBy('month')
//                     ->orderBy('month', 'ASC');

//                 $result = $queryBuilder->getQuery()->getResult();

//                 $chartData = [
//                     'months' => array_column($result, 'month'),
//                     'order_counts' => array_column($result, 'order_count')
//                 ];

//                 return $this->render('dashboard/index.html.twig', [
//                     'chartData' => $serializer->serialize($chartData, 'json')
//                 ]);
//             } catch (\Exception $e) {
//                 $logger->error('Error generating chart data: ' . $e->getMessage());
//                 return new Response('An error occurred while generating the chart data.', Response::HTTP_INTERNAL_SERVER_ERROR);
//             }
//         }

//         return $this->render('dashboard/home.html.twig', [
//             'controller_name' => 'DashboardController',
//         ]);
//     }
// }

// namespace App\Controller;

// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\Annotation\Route;
// use App\Repository\ProductRepository;
// use App\Repository\OrderRepository;

// class DashboardController extends AbstractController
// {
//     #[Route('/dashboard', name: 'home')]
//     public function index(ProductRepository $productRepository, OrderRepository $orderRepository): Response
//     {
//         $user = $this->getUser();
//         if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {
//             // // Get product count
//             // $productCount = count($productRepository->findAll());

//             // // Get order count
//             // $orderCount = count($orderRepository->findAll());

//             return $this->render('dashboard/index.html.twig', [
//                 // 'productCount' => $productCount,
//                 // 'orderCount' => $orderCount,

//             ]);
//         }

//         return $this->render('dashboard/home.html.twig', [
//             'controller_name' => 'DashboardController',
//         ]);
//     }
// }

