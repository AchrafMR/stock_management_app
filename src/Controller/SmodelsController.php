<?php
namespace App\Controller;

use App\Entity\Smodels;
use App\Form\SmodelsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/smodels')]
class SmodelsController extends AbstractController
{
    #[Route('/', name: 'app_smodels_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('smodels/index.html.twig');
    }

    #[Route('/new', name: 'app_smodels_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $smodel = new Smodels();
        $form = $this->createForm(SmodelsType::class, $smodel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($smodel);
            $entityManager->flush();
            $this->addFlash('success', 'Sub model has been added successfully!');

            return $this->redirectToRoute('app_smodels_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('smodels/new.html.twig', [
            'smodel' => $smodel,
            'form' => $form,
        ]);
    }

   #[Route('/data', name: 'app_smodels_data', methods: ['GET'])]

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
           ->select('s.id', 's.name', 's.path')
           ->from(Smodels::class, 's');
       // Apply search query
       if (!empty($search)) {
           $queryBuilder->andWhere('s.name LIKE :search OR s.path LIKE :search')
               ->setParameter('search', "%$search%");
       }
       if (!empty($orderColumn)) {
           $queryBuilder->orderBy("s.$orderColumn", $orderDir);
       }
       $totalRecords = $em->createQueryBuilder()
           ->select('COUNT(s.id)')
           ->from(Smodels::class, 's')
           ->getQuery()
           ->getSingleScalarResult();

       $queryBuilder->setFirstResult($start)
           ->setMaxResults($length);

       $results = $queryBuilder->getQuery()->getResult();
       $formattedData = [];
       foreach ($results as $smodel) {
           $formattedData[] = [
               'id' => $smodel['id'],
               'name' => $smodel['name'],
               'path' => $smodel['path'],
               'actions' => $this->renderView('smodels/_actions.html.twig', ['smodel' => $smodel]),
           ];
       }
       return new JsonResponse([
           'draw' =>$draw,
           'recordsTotal' => $totalRecords,
           'recordsFiltered' => $totalRecords,
           'data' => $formattedData,
       ]);
   }


    #[Route('/{id}/edit', name: 'app_smodels_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Smodels $smodel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SmodelsType::class, $smodel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Sub model has been updated successfully!');

            return $this->redirectToRoute('app_smodels_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('smodels/edit.html.twig', [
            'smodel' => $smodel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_smodels_delete', methods: ['POST'])]
    public function delete(Request $request, Smodels $smodel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$smodel->getId(), $request->request->get('_token'))) {
            $entityManager->remove($smodel);
            $entityManager->flush();
            $this->addFlash('success', 'Sub model has been deleted successfully!');

        }

        return $this->redirectToRoute('app_smodels_index', [], Response::HTTP_SEE_OTHER);
    }
}

