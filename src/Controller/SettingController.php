<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/setting')]
class SettingController extends AbstractController
{
    #[Route('/', name: 'app_setting_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // $currentUser = $this->getUser();
        // $roles = $currentUser ? $currentUser->getRoles() : [];
        return $this->render('setting/index.html.twig', [
            'users' => $userRepository->findAll(),
            // 'roles' => $roles
        ]);
    }

    #[Route('/new', name: 'app_setting_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_setting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('setting/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/user/data', name: 'app_user_data', methods: ['GET'])]
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
            ->select('u.id', 'u.email', 'u.roles', 'u.username')
            ->from(User::class, 'u');

        // Apply search query
        if (!empty($search)) {
            $queryBuilder->andWhere('u.email LIKE :search OR u.username LIKE :search')
                ->setParameter('search', "%$search%");
        }

        // Apply ordering
        if (!empty($orderColumn)) {
            $queryBuilder->orderBy("u.$orderColumn", $orderDir);
        }

        $totalRecords = $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(User::class, 'u')
            ->getQuery()
            ->getSingleScalarResult();

        $queryBuilder->setFirstResult($start)
            ->setMaxResults($length);

        $results = $queryBuilder->getQuery()->getResult();
        $formattedData = [];
        foreach ($results as $user) {
            $role = in_array('ROLE_ADMIN',$user['roles'])?'ADMIN':'USER';
            $formattedData[] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'roles' => $role,
                // 'roles' => json_encode($user['roles']),
                'username' => $user['username'],
                'actions' => in_array('ROLE_ADMIN',$user['roles'])? '': $this->renderView('setting/_actions.html.twig', ['user' => $user ,]),
            ];
        }

        return new JsonResponse([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $formattedData,
        ]);
    }


    #[Route('/{id}', name: 'app_setting_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('setting/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_setting_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_setting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('setting/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_setting_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_setting_index', [], Response::HTTP_SEE_OTHER);
    }
}
