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

                // Verificar si ya existe un turno reservado para esa fecha/hora y servicio
                $existing = $entityManager->getRepository(Turno::class)->createQueryBuilder('t')
                    ->andWhere('t.servicio = :servicio')
                    ->andWhere('t.fecha = :fecha')
                    ->andWhere('t.hora = :hora')
                    ->andWhere('t.estado = :estado')
                    ->setParameter('servicio', $servicio)
                    ->setParameter('fecha', $turno->getFecha()->format('Y-m-d'))
                    ->setParameter('hora', $turno->getHora()->format('H:i:s'))
                    ->setParameter('estado', 'reservado')
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($existing) {
                    $this->addFlash('error', 'El horario seleccionado ya está reservado. Por favor elija otro.');
                } else {
                    $entityManager->persist($turno);
                    $entityManager->flush();

                    $this->addFlash('success', '¡Turno reservado exitosamente!');
                    return $this->redirectToRoute('servicio_publico', ['id' => $servicio->getId()]);
                }
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al reservar el turno: ' . $e->getMessage());
            }
        }

        // Calcular fechas de la semana actual y la semana siguiente (según zona horaria de Argentina)
        $tz = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $today = new \DateTime('now', $tz);

        // Obtener el lunes de la semana actual
        $dayOfWeek = (int)$today->format('N'); // 1 (Lunes) - 7 (Domingo)
        $startOfWeek = (clone $today)->modify('-' . ($dayOfWeek - 1) . ' days')->setTime(0, 0, 0);

        // Rango hasta el fin de la semana siguiente
        $endOfNextWeek = (clone $startOfWeek)->modify('+13 days')->setTime(23, 59, 59);

        // Buscar turnos reservados para este servicio entre startOfWeek y endOfNextWeek
        $turnoRepo = $entityManager->getRepository(Turno::class);
        $qb = $turnoRepo->createQueryBuilder('t')
            ->andWhere('t.servicio = :servicio')
            ->andWhere('t.fecha BETWEEN :start AND :end')
            ->andWhere('t.estado = :estado')
            ->setParameter('servicio', $servicio)
            ->setParameter('start', $startOfWeek->format('Y-m-d'))
            ->setParameter('end', $endOfNextWeek->format('Y-m-d'))
            ->setParameter('estado', 'reservado')
        ;

        $turnosReservados = $qb->getQuery()->getResult();

        // Mapear turnos reservados por fecha => [horas]
        $reserved = [];
        foreach ($turnosReservados as $t) {
            if ($t->getFecha() && $t->getHora()) {
                $dateStr = $t->getFecha()->format('Y-m-d');
                $timeStr = $t->getHora()->format('H:i');
                if (!isset($reserved[$dateStr])) {
                    $reserved[$dateStr] = [];
                }
                $reserved[$dateStr][] = $timeStr;
            }
        }

        // Construir fechas por día de la semana para semana 0 (actual) y 1 (siguiente)
        $weekDates = [0 => [], 1 => []];
        for ($week = 0; $week <= 1; $week++) {
            for ($dayId = 1; $dayId <= 7; $dayId++) {
                $d = (clone $startOfWeek)->modify('+' . (($week * 7) + ($dayId - 1)) . ' days');
                $weekDates[$week][$dayId] = $d->format('Y-m-d');
            }
        }

        return $this->render('servicio/publico.html.twig', [
            'servicio' => $servicio,
            'form' => $form->createView(),
            'reserved' => $reserved,
            'weekDates' => $weekDates,
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