<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PayPalService;

class Paypal extends AbstractController
{
    private $payPalService;

    public function __construct(PayPalService $payPalService)
    {
        $this->payPalService = $payPalService;
    }

    #[Route('/api/orders', methods: ['POST'])]
    public function createOrder(Request $request): Response
    {
        $cart = json_decode($request->getContent(), true)['cart'];
        try {
            $response = $this->payPalService->createOrder($cart);
            return new Response(json_encode($response), Response::HTTP_OK, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode(['error' => 'Failed to create order.']), Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/api/orders/{orderID}/capture', methods: ['POST'])]
    public function captureOrder($orderID): Response
    {
        try {
            $response = $this->payPalService->captureOrder($orderID);
            return new Response(json_encode($response), Response::HTTP_OK, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode(['error' => 'Failed to capture order.']), Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type' => 'application/json']);
        }
    }

    // #[Route('/', methods: ['GET'])]
    // public function index(): Response
    // {
    //     return $this->render('checkout.html');
    // }
}
