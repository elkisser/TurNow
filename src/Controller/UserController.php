<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        $user = $this->getUser();
        $suscripcionActiva = $user ? $user->getSuscripcionActiva() : null;
        
        return $this->render('user/index.html.twig', [
            'suscripcion_activa' => $suscripcionActiva,
        ]);
    }
}
