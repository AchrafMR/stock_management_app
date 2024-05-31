<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\UserInfoFormType;
use App\Form\PasswordUpdateFormType;
use App\Entity\User;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $tokenStorage->getToken()->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }

        $userData = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getUserIdentifier()]);
        if (!$userData) {
            throw $this->createNotFoundException('User not found.');
        }

        // User Info Form
        $userInfoForm = $this->createForm(UserInfoFormType::class, $userData);
        $userInfoForm->handleRequest($request);

        if ($userInfoForm->isSubmitted() && $userInfoForm->isValid()) {
            $entityManager->persist($userData);
            $entityManager->flush();
            $this->addFlash('info_success', 'Your information has been updated successfully!');
            return $this->redirectToRoute('app_profile');
        }

        // Password Update Form
        $passwordUpdateForm = $this->createForm(PasswordUpdateFormType::class);
        $passwordUpdateForm->handleRequest($request);

        if ($passwordUpdateForm->isSubmitted() && $passwordUpdateForm->isValid()) {
            $currentPassword = $passwordUpdateForm->get('currentPassword')->getData();
            $plainPassword = $passwordUpdateForm->get('plainPassword')->getData();
            $confirmPassword = $passwordUpdateForm->get('confirmPassword')->getData();

            if ($plainPassword !== $confirmPassword) {
                $this->addFlash('password_error', 'The new passwords do not match.');
            } elseif (!$passwordHasher->isPasswordValid($userData, $currentPassword)) {
                $this->addFlash('password_error', 'The current password is incorrect.');
            } else {
                $hashedPassword = $passwordHasher->hashPassword($userData, $plainPassword);
                $userData->setPassword($hashedPassword);
                $entityManager->persist($userData);
                $entityManager->flush();
                $this->addFlash('password_success', 'Your password has been updated successfully!');
                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('profile/index.html.twig', [
            'userInfoForm' => $userInfoForm->createView(),
            'passwordUpdateForm' => $passwordUpdateForm->createView(),
        ]);
    }

    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $user = $tokenStorage->getToken()->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete_account', $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $tokenStorage->setToken(null);
            $this->addFlash('delete_success', 'Your account has been deleted successfully!');
            return $this->redirectToRoute('app_login');
        }

        return $this->redirectToRoute('app_profile');
    }
}
