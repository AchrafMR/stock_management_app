<?php

namespace App\Controller;

use App\Entity\Models;
use App\Form\ModelsType;
// use App\Repository\ModelsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/models')]
class ModelsController extends AbstractController
{
    #[Route('/', name: 'models_index')]
    public function index(): Response
    {
        return $this->render('models/index.html.twig');
    }

    #[Route('/new', name: 'models_new')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $model = new Models();
        $form = $this->createForm(ModelsType::class, $model);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($model);
            $entityManager->flush();
            $this->addFlash('success', 'Model has been added successfully!');


            return $this->redirectToRoute('models_index');
        }

        return $this->render('models/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/data', name: 'models_data',methods: ['GET'])]
    public function getData(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $draw = $request->query->get('draw');
        $start = $request->query->get('start') ?? 0;
        $length = $request->query->get('length') ?? 10;
        $search = $request->query->all('search')['value']  ?? '';
        $orderColumnIndex = $request->query->all('order')[0]['column'];
        $orderColumn = $request->query->all('columns')[$orderColumnIndex]['data'];
        $orderDir = $request->query->all('order')[0]['dir'] ?? 'asc';
    
        $queryBuilder = $em->createQueryBuilder()
            ->select('m.id', 'm.name', 'm.path', 'm.icon', 'm.roles')
            ->from(Models::class, 'm');

        // Apply search query
        if (!empty($search)) {
            $queryBuilder->andWhere('m.name LIKE :search OR m.path LIKE :search OR m.icon LIKE :search OR m.roles LIKE :search')
                ->setParameter('search', "%$search%");
        }
    
        if (!empty($orderColumn)) {
            $queryBuilder->orderBy("m.$orderColumn", $orderDir);
        }
    
        $totalRecords = $em->createQueryBuilder()
            ->select('COUNT(m.id)')
            ->from(Models::class, 'm')
            ->getQuery()
            ->getSingleScalarResult();

        $queryBuilder->setFirstResult($start)
            ->setMaxResults($length);

        $results = $queryBuilder->getQuery()->getResult();
        $formattedData = [];
        foreach ($results as $model) {
        $role = in_array('ROLE_ADMIN',$model['roles'])?'ADMIN':'USER';
            $formattedData[] = [
                'id' => $model['id'],
                'name' => $model['name'],
                'path' => $model['path'],
                'icon' => $model['icon'],
                'roles' => $role,
                'actions' => $this->renderView('models/_actions.html.twig', ['model' => $model]),
            ];
        }

        return new JsonResponse([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $formattedData,
        ]);
    }

    #[Route('/edit/{id}', name: 'models_edit')]
    public function edit(Request $request, Models $model, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ModelsType::class, $model);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Model has been updated successfully!');


            return $this->redirectToRoute('models_index');
        }

        return $this->render('models/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'models_delete', methods: ['POST'])]
    public function delete(Request $request, Models $model, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $model->getId(), $request->request->get('_token'))) {
            $entityManager->remove($model);
            $entityManager->flush();
            $this->addFlash('success', 'Model has been deleted successfully!');

        }

        return $this->redirectToRoute('models_index');
    }
}

