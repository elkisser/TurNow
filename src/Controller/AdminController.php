<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Servicio;
use App\Entity\Suscripcion;
use App\Form\ServicioType;
use App\Services\TurnoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        // Obtener todos los usuarios
        $usuarios = $entityManager->getRepository(Usuario::class)->findAll();
        
        // Obtener suscripciones próximas a vencer (menos de 7 días)
        $suscripcionesProximasVencer = [];
        $suscripcionesVencidas = [];
        $totalServicios = 0;
        
        foreach ($usuarios as $usuario) {
            $suscripcionActiva = $usuario->getSuscripcionActiva();
            
            if ($suscripcionActiva) {
                $estado = $suscripcionActiva->getEstado();
                
                if ($estado === 'proxima_vencer') {
                    $suscripcionesProximasVencer[] = $suscripcionActiva;
                } elseif ($estado === 'vencida') {
                    $suscripcionesVencidas[] = $suscripcionActiva;
                }
            }
            
            $totalServicios += count($usuario->getServicios());
        }
        
        return $this->render('admin/dashboard.html.twig', [
            'usuarios' => $usuarios,
            'suscripciones_proximas_vencer' => $suscripcionesProximasVencer,
            'suscripciones_vencidas' => $suscripcionesVencidas,
            'total_servicios' => $totalServicios
        ]);
    }
    
    #[Route('/usuario/{id}/editar', name: 'admin_usuario_editar')]
    public function editarUsuario(Usuario $usuario, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Lógica para editar usuario y suscripción
        if ($request->isMethod('POST')) {
            $plan = $request->request->get('plan');
            $duracionExtension = $request->request->get('duracion_extension');
            
            // Actualizar roles según el plan seleccionado
            $roles = ['ROLE_USER'];
            switch ($plan) {
                case 'basico':
                    $roles[] = 'ROLE_PLAN_BASICO';
                    break;
                case 'profesional':
                    $roles[] = 'ROLE_PLAN_PROFESIONAL';
                    break;
                case 'empresa':
                    $roles[] = 'ROLE_PLAN_EMPRESA';
                    break;
            }
            
            $usuario->setRoles($roles);
            
            // Crear o actualizar suscripción
            $suscripcion = $usuario->getSuscripcionActiva();
            if (!$suscripcion) {
                $suscripcion = new Suscripcion();
                $suscripcion->setUsuario($usuario);
                $suscripcion->setFechaInicio(new \DateTimeImmutable());
                $entityManager->persist($suscripcion);
            }
            
            $suscripcion->setTipo($plan);
            
            // Extender suscripción si se especificó una duración
            if ($duracionExtension) {
                $fechaFin = $suscripcion->getFechaFin() ?: new \DateTimeImmutable();
                $fechaFin = $fechaFin->modify("+$duracionExtension months");
                $suscripcion->setFechaFin($fechaFin);
            }
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Usuario actualizado correctamente');
            return $this->redirectToRoute('admin_dashboard');
        }
        
        return $this->render('admin/editar_usuario.html.twig', [
            'usuario' => $usuario
        ]);
    }
    
    #[Route('/usuario/nuevo', name: 'admin_usuario_nuevo')]
    public function nuevoUsuario(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            // Crear nuevo usuario
            $usuario = new Usuario();
            $usuario->setNombre($request->request->get('nombre'));
            $usuario->setEmail($request->request->get('email'));
            $usuario->setPassword("1234"); // Contraseña temporal
            
            // Asignar roles según el plan
            $plan = $request->request->get('plan');
            $roles = ['ROLE_USER'];

            // Usamos match y filtramos nulls automáticamente
            $rolePlan = match($plan) {
                'basico' => 'ROLE_PLAN_BASICO',
                'profesional' => 'ROLE_PLAN_PROFESIONAL',
                'empresa' => 'ROLE_PLAN_EMPRESA',
                default => null,
            };

            if ($rolePlan !== null) {
                $roles[] = $rolePlan;
            }
            
            $usuario->setRoles($roles);
            
            // Crear suscripción
            $suscripcion = new Suscripcion();
            $suscripcion->setUsuario($usuario);
            $suscripcion->setTipo($plan);
            $suscripcion->setFechaInicio(new \DateTimeImmutable());
            
            $duracion = (int) $request->request->get('duracion');
            $fechaFin = (new \DateTimeImmutable())->modify("+$duracion months");
            $suscripcion->setFechaFin($fechaFin);
            
            $entityManager->persist($usuario);
            $entityManager->persist($suscripcion);
            $entityManager->flush();
            
            $this->addFlash('success', 'Usuario creado correctamente');
            return $this->redirectToRoute('admin_dashboard');
        }
        
        return $this->render('admin/nuevo_usuario.html.twig');
    }
}