<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/products/data', name: 'app_product_data', methods: ['GET'])]
    public function getData(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $draw = $request->query->get('draw');
        $start = $request->query->get('start') ?? 0;
        $length = $request->query->get('length') ?? 10;
        $search = $request->query->all('search')['value'] ?? '';
        $orderColumnIndex = $request->query->all('order')[0]['column'];
        $orderColumn = $request->query->all('columns')[$orderColumnIndex]['data'];
        $orderDir = $request->query->all('order')[0]['dir'] ?? 'asc';

        $queryBuilder = $em->createQueryBuilder()
            ->select('p.id', 'p.name', 'p.stock', 'p.price', 'p.description', 'p.image')
            ->from(Product::class, 'p');

        // Apply search query
        if (!empty($search)) {
            $queryBuilder->andWhere('p.name LIKE :search OR p.description LIKE :search OR p.stock LIKE :search OR p.price LIKE :search OR p.image LIKE :search')
                ->setParameter('search', "%$search%");
        }

        // Apply ordering
        if (!empty($orderColumn)) {
            $queryBuilder->orderBy("p.$orderColumn", $orderDir);
        }

        // Get total records count
        $totalRecords = $em->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->getQuery()
            ->getSingleScalarResult();

        // Apply pagination
        $queryBuilder->setFirstResult($start)
            ->setMaxResults($length);

        $results = $queryBuilder->getQuery()->getResult();
        $formattedData = [];
        foreach ($results as $product) {
            $formattedData[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'stock' => $product['stock'],
                'price' => $product['price'],
                'description' => $product['description'],
                'image' => $product['image'],
                'actions' => $this->renderView('product/_actions.html.twig', ['product' => $product]),
            ];
        }

        return new JsonResponse([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $formattedData,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
