<?php
namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    #[Route('/user/order', name: 'app_order_form')]
    public function orderForm(Request $request, SessionInterface $session, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cart = $session->get('cart', []);
            foreach ($cart as $productId => $quantity) {
                $product = $productRepository->find($productId);
                if ($product) {
                    $orderItem = new OrderItem();
                    $orderItem->setProduct($product);
                    $orderItem->setQuantity($quantity);
                    $orderItem->setTotal($product->getPrice() * $quantity);
                    $orderItem->setOrders($order);
                    $entityManager->persist($orderItem);
                }
            }
            $order->setDate(new \DateTime());
            $order->setUser($this->getUser()); // assuming the user is logged in
            $entityManager->persist($order);
            $entityManager->flush();

            $session->remove('cart');
            $this->addFlash('success', 'Your order has been placed successfully.');

            return $this->redirectToRoute('user_order_index');
        }

        return $this->render('order/order_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/user/orders', name: 'user_order_index')]
    public function userOrderIndex(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $orders = $orderRepository->findBy(['user' => $user]);

        return $this->render('order/user_index.html.twig', [
            'orders' => $orders,
        ]);
    }
    #[Route('/admin/orders', name: 'admin_order_index')]
    public function adminOrderIndex(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAll();

        return $this->render('order/admin_index.html.twig', [
            'orders' => $orders,
        ]);
    }
}

