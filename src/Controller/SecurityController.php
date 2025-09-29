<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si el usuario ya está autenticado, redirigir según su tipo
        if ($this->getUser()) {
            return $this->redirectBasedOnUserType();
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Redirige al usuario según su tipo y plan
     */
    private function redirectBasedOnUserType(): Response
    {
        $user = $this->getUser();
        
        // Si es administrador (tiene ROLE_ADMIN), ir al dashboard de admin
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('admin_dashboard');
        }
        
        // Si tiene una suscripción activa con plan, ir a la vista de usuario
        $suscripcionActiva = $user->getSuscripcionActiva();
        if ($suscripcionActiva && $suscripcionActiva->isActiva()) {
            $plan = $suscripcionActiva->getTipo();
            
            // Verificar que el plan sea válido
            if (in_array($plan, ['basico', 'profesional', 'empresa'])) {
                return $this->redirectToRoute('app_user');
            }
        }
        
        // Si no tiene plan o suscripción activa, ir al dashboard de admin por defecto
        return $this->redirectToRoute('admin_dashboard');
    }
}
