<?php

namespace App\Controller;

use App\Repository\ModelsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ModelsController extends AbstractController
{
    #[Route('/dashboard/models', name:'models')]
    public function index(ModelsRepository $modelsRepository): Response
    {

        $models=$modelsRepository->findAll();
        // dd($models);
        return $this->render('models/index.html.twig', [
            'models' => $models,
        ]);
    }
}
