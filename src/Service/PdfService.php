<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfService
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generatePdf($template, $data)
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->twig->render($template, $data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}










    // #[Route('/data', name: 'products_data')]
    // public function getData(Request $request, ProductRepository $productRepository): JsonResponse
    // {
    //     $draw = intval($request->get('draw'));
    //     $start = intval($request->get('start'));
    //     $length = intval($request->get('length'));
    //     $search = $request->get('search')['value'];
    //     $orderColumnIndex = intval($request->get('order')[0]['column']);
    //     $orderDirection = $request->get('order')[0]['dir'];
    //     $columns = $request->get('columns');
    //     $orderColumn = $columns[$orderColumnIndex]['data'];

    //     $queryBuilder = $productRepository->createQueryBuilder('m')->setFirstResult($start)->setMaxResults($length);

    //     if (!empty($orderColumn)) {
    //         $queryBuilder->orderBy("m.".$orderColumn, $orderDirection);
    //     }

    //     if (!empty($search)) {
    //         $queryBuilder->where('m.name LIKE :search OR m.stock LIKE :search OR m.price LIKE :search')
    //             ->setParameter('search', "%".$search."%");
    //     }

    //     $totalRecords = $productRepository->count([]);
    //     $results = $queryBuilder->getQuery()->getResult();
    //     $formattedData = [];
    //     foreach ($results as $product) {
    //         $formattedData[] = [
    //             'id' => $product->getId(),
    //             'name' => $product->getName(),
    //             'stock' => $product->getStock(),
    //             'price' => $product->getPrice(),
    //             'category' => $product->getCategory() ? $product->getCategory()->getName() : 'N/A',
    //         ];
    //     }
    //     return new JsonResponse([
    //         'draw' => $draw,
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => count($formattedData),
    //         'data' => $formattedData,
    //     ]);
    // }