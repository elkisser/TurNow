<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        $suscripcionActiva = $user->getSuscripcionActiva();
        $servicios = $user->getServicios();
        
        return $this->render('user/index.html.twig', [
            'suscripcion_activa' => $suscripcionActiva,
            'servicios' => $servicios,
        ]);
    }
}
