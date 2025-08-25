<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TurnoController extends AbstractController
{
    #[Route('/api/turno', name: 'app_api_turno')]
    public function index(): Response
    {
        return $this->render('api/turno/index.html.twig', [
            'controller_name' => 'TurnoController',
        ]);
    }
}
