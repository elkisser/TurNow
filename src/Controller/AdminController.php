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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
        if ($request->isMethod('POST')) {
            $plan = $request->request->get('plan');
            $duracionExtension = (int) $request->request->get('duracion_extension', '0');
            
            // Debug: Log de los datos recibidos
            error_log("Plan recibido: " . $plan);
            error_log("Duración extensión: " . $duracionExtension);
            
            // Actualizar datos básicos del usuario
            $usuario->setNombre($request->request->get('nombre'));
            $usuario->setEmail($request->request->get('email'));
            
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
            
            // Obtener suscripción activa
            $suscripcion = $usuario->getSuscripcionActiva();
            
            if (!$suscripcion) {
                // Crear nueva suscripción
                $suscripcion = new Suscripcion();
                $suscripcion->setUsuario($usuario);
                $suscripcion->setTipo($plan);
                $suscripcion->setFechaInicio(new \DateTimeImmutable());
                $suscripcion->setActiva(true);
                $entityManager->persist($suscripcion);
                
                error_log("Nueva suscripción creada para plan: " . $plan);
                
                // Solo establecer fecha_fin si se proporcionó una duración
                if ($duracionExtension && is_numeric($duracionExtension)) {
                    $fechaFin = $this->calcularFechaVencimiento(new \DateTimeImmutable(), $duracionExtension);
                    $suscripcion->setFechaFin($fechaFin);
                    error_log("Fecha fin establecida: " . $fechaFin->format('Y-m-d H:i:s'));
                }
            } else {
                // Actualizar suscripción existente
                $suscripcion->setTipo($plan);
                $suscripcion->setActiva(true);
                
                error_log("Suscripción existente actualizada para plan: " . $plan);
                
                // Extender suscripción solo si se especificó una duración
                if ($duracionExtension > 0) {
                    $fechaFinActual = $suscripcion->getFechaFin();
                    
                    if ($fechaFinActual) {
                        // Si ya tiene fecha_fin, calcular desde la fecha de inicio (no extender)
                        // Calcular desde la fecha de inicio de la suscripción
                        $fechaFin = $this->calcularFechaVencimiento($suscripcion->getFechaInicio(), $duracionExtension);
                    } else {
                        // Si no tiene fecha_fin, calcular desde la fecha actual
                        $fechaFin = $this->calcularFechaVencimiento(new \DateTimeImmutable(), $duracionExtension);
                    }
                    
                    $suscripcion->setFechaFin($fechaFin);
                    error_log("Fecha fin extendida: " . $fechaFin->format('Y-m-d H:i:s'));
                }
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
    public function nuevoUsuario(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            // Crear nuevo usuario
            $usuario = new Usuario();
            $usuario->setNombre($request->request->get('nombre'));
            $usuario->setEmail($request->request->get('email'));
            
            // Generar contraseña temporal y hashearla
            $plainPassword = $request->request->get('password') ?: 'temp_password_123';
            $hashedPassword = $passwordHasher->hashPassword($usuario, $plainPassword);
            $usuario->setPassword($hashedPassword);
            
            // Asignar roles según el plan
            $plan = $request->request->get('plan');
            $roles = ['ROLE_USER'];

            switch($plan) {
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
            
            // Crear suscripción
            $suscripcion = new Suscripcion();
            $suscripcion->setUsuario($usuario);
            $suscripcion->setTipo($plan);
            $suscripcion->setFechaInicio(new \DateTimeImmutable());
            $suscripcion->setActiva(true);
            
            // Establecer fecha_fin solo si se proporciona duración
            $duracion = (int) $request->request->get('duracion');
            if ($duracion > 0) {
                $fechaFin = $this->calcularFechaVencimiento(new \DateTimeImmutable(), $duracion);
                $suscripcion->setFechaFin($fechaFin);
            }
            
            $entityManager->persist($usuario);
            $entityManager->persist($suscripcion);
            $entityManager->flush();
            
            $this->addFlash('success', 'Usuario creado correctamente');
            return $this->redirectToRoute('admin_dashboard');
        }
        
        return $this->render('admin/nuevo_usuario.html.twig');
    }
    
    /**
     * Calcula la fecha de vencimiento agregando meses de forma precisa
     * Maneja correctamente años bisiestos y meses con diferentes números de días
     */
    private function calcularFechaVencimiento(\DateTimeImmutable $fechaInicio, int $meses): \DateTimeImmutable
    {
        $año = (int) $fechaInicio->format('Y');
        $mes = (int) $fechaInicio->format('n');
        $dia = (int) $fechaInicio->format('j');
        $hora = (int) $fechaInicio->format('G');
        $minuto = (int) $fechaInicio->format('i');
        $segundo = (int) $fechaInicio->format('s');
        
        // Calcular el año y mes final
        $mesFinal = $mes + $meses;
        $añoFinal = $año + intval(($mesFinal - 1) / 12);
        $mesFinal = (($mesFinal - 1) % 12) + 1;
        
        // Ajustar el día si es necesario (para casos como 31 de enero + 1 mes = 28/29 de febrero)
        $diasEnMesFinal = cal_days_in_month(CAL_GREGORIAN, $mesFinal, $añoFinal);
        $diaFinal = min($dia, $diasEnMesFinal);
        
        // Crear la fecha final
        $fechaFin = new \DateTimeImmutable();
        $fechaFin = $fechaFin->setDate($añoFinal, $mesFinal, $diaFinal);
        $fechaFin = $fechaFin->setTime($hora, $minuto, $segundo);
        
        return $fechaFin;
    }
}
