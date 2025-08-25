<?php

namespace App\Controller;

use App\Entity\Servicio;
use App\Entity\Turno;
use App\Form\ReservaTurnoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServicioPublicoController extends AbstractController
{
    #[Route('/servicio/{id}', name: 'servicio_publico')]
    public function showServicio(Servicio $servicio, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Crear formulario de reserva
        $turno = new Turno();
        $form = $this->createForm(ReservaTurnoType::class, $turno);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $turno->setServicio($servicio);
                $turno->setEstado('reservado');
                
                $entityManager->persist($turno);
                $entityManager->flush();
                
                $this->addFlash('success', '¡Turno reservado exitosamente!');
                return $this->redirectToRoute('servicio_publico', ['id' => $servicio->getId()]);
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al reservar el turno: ' . $e->getMessage());
            }
        }

        return $this->render('servicio/publico.html.twig', [
            'servicio' => $servicio,
            'form' => $form->createView()
        ]);
    }

    #[Route('/mis-servicios', name: 'mis_servicios')]
    public function misServicios(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $servicios = $this->getUser()->getServicios();
        
        return $this->render('admin/mis_servicios.html.twig', [
            'servicios' => $servicios
        ]);
    }
}