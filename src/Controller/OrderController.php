<?php
namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderItem;
use App\Service\PdfService;
use App\Service\RpdfService;
use Psr\Log\LoggerInterface;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    private $pdfService;
    private $rpdfService;

    public function __construct(PdfService $pdfService,RpdfService $rpdfService)
    {
        $this->pdfService = $pdfService;
        $this->rpdfService = $rpdfService;
    }

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
                    // Check if the product has enough stock
                    if ($product->getStock() >= $quantity) {
                        // Decrease product stock
                        $product->setStock($product->getStock() - $quantity);

                        $orderItem = new OrderItem();
                        $orderItem->setProduct($product);
                        $orderItem->setQuantity($quantity);
                        $orderItem->setTotal($product->getPrice() * $quantity);
                        $orderItem->setOrders($order);
                        $entityManager->persist($orderItem);

                        // Persist the updated product stock
                        $entityManager->persist($product);
                    } else {
                        // Handle the case where the product does not have enough stock
                        $this->addFlash('error', 'Not enough stock for product: ' . $product->getName());
                        return $this->redirectToRoute('app_user_product');
                    }
                }
            }
            $order->setDate(new \DateTime());
            $order->setUser($this->getUser()); // assuming the user is logged in
            $entityManager->persist($order);
            $entityManager->flush();

            $session->remove('cart');
            $this->addFlash('success', 'Your order has been placed successfully.');

            return $this->redirectToRoute('payment', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('order/order_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/order/{id}', name: 'payment')]
    public function payment(Order $order): Response
    {
        return $this->render('order/payment.html.twig', [
            'order' => $order,
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
    #[Route('/admin/orders/data', name: 'orders_data')]
    public function getOrderData(Request $request, OrderRepository $orderRepository, LoggerInterface $logger): JsonResponse
    {
        $draw = intval($request->get('draw'));
        $start = intval($request->get('start'));
        $length = intval($request->get('length'));
        $search = $request->get('search')['value'] ?? '';
        $orderColumnIndex = intval($request->get('order')[0]['column']);
        $orderDirection = $request->get('order')[0]['dir'];
        $columns = $request->get('columns');
        $orderColumn = $columns[$orderColumnIndex]['data'];
    
        $logger->info("Request parameters: draw=$draw, start=$start, length=$length, search=$search, orderColumn=$orderColumn, orderDirection=$orderDirection");
    
        $queryBuilder = $orderRepository->createQueryBuilder('o')
            ->leftJoin('o.user', 'u')
            ->leftJoin('o.orderitem', 'oi')
            ->setFirstResult($start)
            ->setMaxResults($length);
    
        if (!empty($orderColumn)) {
            if ($orderColumn == 'username' || $orderColumn == 'email') {
                $queryBuilder->orderBy("u." . $orderColumn, $orderDirection);
            } else {
                $queryBuilder->orderBy("o." . $orderColumn, $orderDirection);
            }
        }
    
        if (!empty($search)) {
            $queryBuilder->andWhere('u.username LIKE :search OR u.email LIKE :search OR o.adress LIKE :search OR o.phone LIKE :search')
                ->setParameter('search', "%" . $search . "%");
        }
    
        $totalRecords = $orderRepository->count([]);
        $filteredRecords = count($queryBuilder->getQuery()->getResult());
    
        $queryBuilder->select('o', 'u', 'oi');
        $results = $queryBuilder->getQuery()->getResult();
        $formattedData = [];
    
        foreach ($results as $order) {
            $total = 0;
            foreach ($order->getOrderitem() as $item) {
                $total += $item->getTotal();
            }
    
            $formattedData[] = [
                'id' => $order->getId(),
                'username' => $order->getUser() ? $order->getUser()->getUsername() : '',
                'email' => $order->getUser() ? $order->getUser()->getEmail() : '',
                'adress' => $order->getAdress(),
                'phone' => $order->getPhone(),
                'date' => $order->getDate()->format('Y-m-d H:i:s'),
                'total' => $total,
            ];
        }
    
        $logger->info('Response data prepared', ['formattedData' => $formattedData]);
    
        return new JsonResponse([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData,
        ]);
    }
    


    #[Route('/user/orders/{id}/pdf', name: 'user_order_pdf')]
    public function downloadOrderPdf(Order $order): StreamedResponse
    {
        $total = 0;
        foreach ($order->getOrderitem() as $item) {
            $total += $item->getTotal();
        }

        $pdfContent = $this->pdfService->generatePdf('order/pdf.html.twig', [
            'order' => $order,
            'total' => $total,
        ]);

        $response = new StreamedResponse(function() use ($pdfContent) {
            echo $pdfContent;
        });

        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="order_' . $order->getId() . '.pdf"');

        return $response;
    }

    #[Route('/user/orders/{id}/receipt', name: 'user_order_receipt')]
    public function downloadOrderReceipt(Order $order): StreamedResponse
    {
        $total = 0;
        foreach ($order->getOrderitem() as $item) {
            $total += $item->getTotal();
        }

        $pdfContent = $this->rpdfService->generatePdf('order/receipt.html.twig', [
            'order' => $order,
            'total' => $total,
        ]);

        $response = new StreamedResponse(function() use ($pdfContent) {
            echo $pdfContent;
        });

        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="receipt_' . $order->getId() . '.pdf"');

        return $response;
    }

}
