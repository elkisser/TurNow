<?php

namespace App\Services;

use App\Entity\Turno;
use App\Entity\Servicio;
use App\Entity\UsuarioTemporal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TurnoService
{
    private $entityManager;
    private $mailer;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    /**
     * Crear un nuevo turno
     */
    public function crearTurno(Servicio $servicio, \DateTimeInterface $fecha, \DateTimeInterface $hora): Turno
    {
        $turno = new Turno();
        $turno->setServicio($servicio);
        $turno->setFecha($fecha);
        $turno->setHora($hora);
        $turno->setEstado('disponible');

        $this->entityManager->persist($turno);
        $this->entityManager->flush();

        return $turno;
    }

    /**
     * Reservar un turno
     */
    public function reservarTurno(Turno $turno, string $email): Turno
    {
        // Verificar si el usuario temporal ya existe
        $usuarioTemporal = $this->entityManager->getRepository(UsuarioTemporal::class)
            ->findOneBy(['email' => $email]);

        if (!$usuarioTemporal) {
            $usuarioTemporal = new UsuarioTemporal();
            $usuarioTemporal->setEmail($email);
            $this->entityManager->persist($usuarioTemporal);
        }

        $turno->setUsuarioTemporal($usuarioTemporal);
        $turno->setUsuarioEmail($email);
        $turno->setEstado('reservado');

        $this->entityManager->flush();

        return $turno;
    }

    /**
     * Cancelar un turno
     */
    public function cancelarTurno(Turno $turno): Turno
    {
        $turno->setUsuarioTemporal(null);
        $turno->setUsuarioEmail(null);
        $turno->setEstado('disponible');

        $this->entityManager->flush();

        return $turno;
    }

    /**
     * Verificar disponibilidad de turno
     */
    public function verificarDisponibilidad(Servicio $servicio, \DateTimeInterface $fecha, \DateTimeInterface $hora): bool
    {
        $turnoExistente = $this->entityManager->getRepository(Turno::class)
            ->findOneBy([
                'servicio' => $servicio,
                'fecha' => $fecha,
                'hora' => $hora,
                'estado' => 'reservado'
            ]);

        return $turnoExistente === null;
    }

    /**
     * Obtener turnos por servicio y fecha
     */
    public function getTurnosPorServicioYFecha(Servicio $servicio, \DateTimeInterface $fecha): array
    {
        return $this->entityManager->getRepository(Turno::class)
            ->findBy([
                'servicio' => $servicio,
                'fecha' => $fecha
            ]);
    }

    /**
     * Enviar email de confirmación
     */
    public function enviarEmailConfirmacion(Turno $turno): void
    {
        $email = (new Email())
            ->from('no-reply@turnow.com')
            ->to($turno->getUsuarioEmail())
            ->subject('Confirmación de turno - TurNow')
            ->html($this->generarTemplateEmailConfirmacion($turno));

        $this->mailer->send($email);
    }

    private function generarTemplateEmailConfirmacion(Turno $turno): string
    {
        return sprintf(
            '<h1>Confirmación de Turno</h1>
            <p>Su turno ha sido confirmado exitosamente.</p>
            <p><strong>Servicio:</strong> %s</p>
            <p><strong>Fecha:</strong> %s</p>
            <p><strong>Hora:</strong> %s</p>
            <p><strong>Estado:</strong> %s</p>
            <br>
            <p>Gracias por usar TurNow!</p>',
            $turno->getServicio()->getNombre(),
            $turno->getFecha()->format('d/m/Y'),
            $turno->getHora()->format('H:i'),
            $turno->getEstado()
        );
    }

    /**
     * Obtenemos las estadisticas del usuario
     */
    public function getEstadisticasUsuario($usuario): array
    {
        $servicioRepository = $this->entityManager->getRepository(Servicio::class);
        $turnoRepository = $this->entityManager->getRepository(Turno::class);
        
        $servicios = $servicioRepository->findBy(['administrador' => $usuario]);
        $totalServicios = count($servicios);
        
        $totalTurnos = 0;
        $turnosReservados = 0;
        $turnosDisponibles = 0;
        
        foreach ($servicios as $servicio) {
            $turnos = $turnoRepository->findBy(['servicio' => $servicio]);
            $totalTurnos += count($turnos);
            
            foreach ($turnos as $turno) {
                if ($turno->getEstado() === 'reservado') {
                    $turnosReservados++;
                } else {
                    $turnosDisponibles++;
                }
            }
        }
        
        return [
            'total_servicios' => $totalServicios,
            'total_turnos' => $totalTurnos,
            'turnos_reservados' => $turnosReservados,
            'turnos_disponibles' => $turnosDisponibles
        ];
    }

    /**
     * Obtener datos reales para el gráfico
     */
    public function getDatosGrafica($usuario): array
    {
        $servicioRepository = $this->entityManager->getRepository(Servicio::class);
        $servicios = $servicioRepository->findBy(['administrador' => $usuario]);
        
        // Datos de los últimos 7 días
        $fechas = [];
        $datos = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $fecha = new \DateTime("-$i days");
            $fechas[] = $fecha->format('D');
            
            $totalReservas = 0;
            foreach ($servicios as $servicio) {
                $reservasDia = $this->entityManager->getRepository(Turno::class)
                    ->createQueryBuilder('t')
                    ->where('t.servicio = :servicio')
                    ->andWhere('t.fecha = :fecha')
                    ->andWhere('t.estado = :estado')
                    ->setParameter('servicio', $servicio)
                    ->setParameter('fecha', $fecha->format('Y-m-d'))
                    ->setParameter('estado', 'reservado')
                    ->getQuery()
                    ->getResult();
                
                $totalReservas += count($reservasDia);
            }
            
            $datos[] = $totalReservas;
        }
        
        return [
            'labels' => $fechas,
            'data' => $datos
        ];
    }

    /**
     * Obtener actividad reciente real
     */
    public function getActividadReciente($usuario): array
    {
        $servicioRepository = $this->entityManager->getRepository(Servicio::class);
        $servicios = $servicioRepository->findBy(['administrador' => $usuario]);
        
        $actividad = [];
        
        foreach ($servicios as $servicio) {
            $turnosRecientes = $this->entityManager->getRepository(Turno::class)
                ->createQueryBuilder('t')
                ->where('t.servicio = :servicio')
                ->orderBy('t.fecha', 'DESC')
                ->addOrderBy('t.hora', 'DESC')
                ->setParameter('servicio', $servicio)
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
            
            foreach ($turnosRecientes as $turno) {
                $actividad[] = [
                    'tipo' => $turno->getEstado() === 'reservado' ? 'reserva' : 'disponible',
                    'servicio' => $servicio->getNombre(),
                    'cliente' => $turno->getUsuarioEmail(),
                    'fecha' => $turno->getFecha(),
                    'hora' => $turno->getHora(),
                    'timestamp' => $turno->getFecha()->format('Y-m-d') . ' ' . $turno->getHora()->format('H:i:s')
                ];
            }
        }
        
        // Ordenar por timestamp más reciente
        usort($actividad, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return array_slice($actividad, 0, 5); // Solo últimos 5
    }
}