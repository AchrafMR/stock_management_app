<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserCategoryController extends AbstractController
{
    #[Route('/user/category', name: 'app_user_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categorys=$categoryRepository->findAll();


        return $this->render('user/user_category/index.html.twig', [
            'controller_name' => 'UserCategoryController',
            'categorys'=>$categorys,
        ]);
    }
}
