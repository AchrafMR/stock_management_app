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
// use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Service\PdfService;


class OrderController extends AbstractController
{
    private $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
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

            return $this->redirectToRoute('payement');
        }

        return $this->render('order/order_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/orders/payement', name: 'payement')]
    public function payement(): Response
    {
        return $this->render('order/payement.html.twig');
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

        $pdfContent = $this->pdfService->generatePdf('order/receipt.html.twig', [
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

    // #[Route('/admin/orders/data', name: 'app_orders_data', methods: ['GET'])]
    // public function getOrderData(Request $request, EntityManagerInterface $em): JsonResponse
    // {
    //         $draw = $request->query->get('draw');
    //         $start = $request->query->get('start') ?? 0;
    //         $length = $request->query->get('length') ?? 10;
    //         $search = $request->query->all('search')['value']  ?? '';
    //         $orderColumnIndex = $request->query->all('order')[0]['column'];
    //         $orderColumn = $request->query->all('columns')[$orderColumnIndex]['data'];
    //         $orderDir = $request->query->all('order')[0]['dir'] ?? 'asc';

    //     $queryBuilder = $em->createQueryBuilder()
    //         ->select('o.id', 'u.username AS username', 'u.email AS email', 'o.address', 'o.phone', 'o.date')
    //         ->from(Order::class, 'o')
    //         ->Join('o.user', 'u');

    //     if (!empty($search)) {
    //         $queryBuilder->andWhere('u.username LIKE :search OR u.email LIKE :search OR o.address LIKE :search OR o.phone LIKE :search')
    //             ->setParameter('search', "%$search%");
    //     }

    //     if (!empty($orderColumn)) {
    //         $queryBuilder->orderBy("o.$orderColumn", $orderDir);
    //     }

    //     $totalRecords = $em->createQueryBuilder()
    //         ->select('COUNT(o.id)')
    //         ->from(Order::class, 'o')
    //         ->getQuery()
    //         ->getSingleScalarResult();

    //     $queryBuilder->setFirstResult($start)
    //         ->setMaxResults($length);

    //     $results = $queryBuilder->getQuery()->getResult();
    //     $formattedData = [];
    //     foreach ($results as $order) {
    //         $orderEntity = $em->getRepository(Order::class)->find($order['id']);
    //         $total = 0;
    //         foreach ($orderEntity->getOrderItems() as $item) {
    //             $total += $item->getTotal();
    //         }
    //         $formattedData[] = [
    //             'id' => $order['id'],
    //             'user_username' => $order['username'],
    //             'user_email' => $order['email'],
    //             'address' => $order['address'],
    //             'phone' => $order['phone'],
    //             'date' => $order['date']->format('Y-m-d H:i:s'),
    //             'total' => $total . ' $',
    //         ];
    //     }

    //     return new JsonResponse([
    //         'draw' => $draw,
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $totalRecords,
    //         'data' => $formattedData,
    //     ]);
    // }

}
