<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServicioController extends AbstractController
{
    #[Route('/servicio', name: 'app_servicio')]
    public function index(): Response
    {
        return $this->render('servicio/index.html.twig', [
            'controller_name' => 'ServicioController',
        ]);
    }
}
