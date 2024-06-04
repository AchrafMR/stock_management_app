<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class RestrictedController extends AbstractController
{
    /**
     * @Route("/restricted", name="restricted")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(): Response
    {
        return new Response('This is a restricted area.');
    }
}
