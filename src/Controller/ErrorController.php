<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Request;

class ErrorController extends AbstractController
{
    public function show(Request $request): Response
    {
        $exception = $request->attributes->get('exception');
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($statusCode == 404) {
            return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
        } elseif ($statusCode == 403) {
            return $this->render('bundles/TwigBundle/Exception/error403.html.twig');
        }

        return $this->render('bundles/TwigBundle/Exception/error.html.twig', ['status_code' => $statusCode]);
    }
}
