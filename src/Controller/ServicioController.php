<?php

namespace App\Controller;

use App\Entity\Servicio;
use App\Form\ServicioType;
use App\Services\TurnoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServicioController extends AbstractController
{
    #[Route('/servicio', name: 'servicio_dashboard')]
    public function dashboard(TurnoService $turnoService): Response
    {
        $user = $this->getUser();
        
        // Obtener estadísticas reales
        $estadisticas = $turnoService->getEstadisticasUsuario($user);
        
        // Obtener datos para el gráfico
        $datosGrafica = $turnoService->getDatosGrafica($user);
        
        // Obtener actividad reciente
        $actividadReciente = $turnoService->getActividadReciente($user);
        
        // Obtener servicios del usuario
        $servicios = $user->getServicios();

        return $this->render('servicio/dashboard.html.twig', [
            'servicios' => $servicios,
            'estadisticas' => $estadisticas,
            'datos_grafica' => $datosGrafica,
            'actividad_reciente' => $actividadReciente
        ]);
    }

    #[Route('/tutorial-completed', name: 'servicio_tutorial_completed', methods: ['POST'])]
    public function markTutorialCompleted(Request $request): Response
    {
        $request->getSession()->set('show_tutorial', false);
        return new Response('Tutorial completed');
    }

    #[Route('/servicios', name: 'servicios')]
    public function servicios(Request $request, EntityManagerInterface $entityManager): Response
    {
        $servicio = new Servicio();
        $form = $this->createForm(ServicioType::class, $servicio);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $usuario = $this->getUser();
                $servicio->setAdministrador($usuario);

                // Procesar banner
                $bannerFile = $form->get('banner_file')->getData();
                if ($bannerFile) {
                    $newFilename = uniqid().'.'.$bannerFile->guessExtension();
                    $bannerFile->move(
                        $this->getParameter('banners_directory'),
                        $newFilename
                    );
                    $servicio->setBannerUrl('/uploads/banners/'.$newFilename);
                }

                // Obtener horarios del campo hidden
                $horariosJson = $form->get('horarios_data')->getData();
                $horarios = [];
                if ($horariosJson) {
                    $horarios = json_decode($horariosJson, true);
                }
                if (!is_array($horarios) || empty($horarios)) {
                    $this->addFlash('error', 'Debes agregar al menos un horario disponible.');
                    // No guardar ni redirigir
                    return $this->render('admin/servicios.html.twig', [
                        'form' => $form->createView(),
                        'servicios' => $this->getUser()->getServicios()
                    ]);
                }
                $servicio->setHorariosDisponibles($horarios);

                $entityManager->persist($servicio);
                $entityManager->flush();

                $this->addFlash('success', '¡Servicio creado exitosamente!');
                return $this->redirectToRoute('servicio_publico', [
                    'slug' => $servicio->getSlug()
                ]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al crear el servicio: ' . $e->getMessage());
            }
        }

        $servicios = $this->getUser()->getServicios();
        return $this->render('servicio/servicios.html.twig', [
            'form' => $form->createView(),
            'servicios' => $servicios
        ]);
    }

    #[Route('/turnos', name: 'servicio_turnos')]
    public function turnos(): Response
    {
        $user = $this->getUser();
        $servicios = $user->getServicios();

        return $this->render('admin/turnos.html.twig', [
            'servicios' => $servicios,
        ]);
    }
}
