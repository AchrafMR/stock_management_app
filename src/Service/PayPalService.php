<?php
namespace App\Service;

// use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PayPalService
{
    private $client;
    private $clientId;
    private $clientSecret;
    private $baseUrl;
    private  $orderRepository;

    public function __construct(HttpClientInterface $client, string $clientId, string $clientSecret, string $baseUrl,OrderRepository $orderRepository)
    {
        $this->client = $client;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl = $baseUrl;
        $this->orderRepository = $orderRepository;
    }

    private function generateAccessToken(): string
    {
        $response = $this->client->request('POST', $this->baseUrl . '/v1/oauth2/token', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ],
            'body' => 'grant_type=client_credentials',
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new HttpException($response->getStatusCode(), 'Failed to generate Access Token');
        }

        $data = $response->toArray();
        return $data['access_token'];
    }

    public function createOrder(array $cart): array
    {

        $accessToken = $this->generateAccessToken();

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '100.00',
                    ],
                ],
            ],
        ];

        $response = $this->client->request('POST', $this->baseUrl . '/v2/checkout/orders', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'json' => $payload,
        ]);

        return $response->toArray();

    
    }

    public function captureOrder(string $orderID): array
    {
        $accessToken = $this->generateAccessToken();

        $response = $this->client->request('POST', $this->baseUrl . '/v2/checkout/orders/' . $orderID . '/capture', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        return $response->toArray();
    }
}
