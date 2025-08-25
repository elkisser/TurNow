<?php

namespace App\Services;

use App\Entity\Servicio;
use Doctrine\ORM\EntityManagerInterface;

class DisponibilidadService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Generar turnos disponibles para un servicio
     */
    public function generarTurnosDisponibles(Servicio $servicio, \DateTimeInterface $fechaInicio, \DateTimeInterface $fechaFin): array
    {
        $turnosGenerados = [];
        $diasTrabajo = $servicio->getDiasTrabajo();
        $horariosDisponibles = $servicio->getHorariosDisponibles();

        $intervalo = new \DateInterval('P1D');
        $periodo = new \DatePeriod($fechaInicio, $intervalo, $fechaFin);

        foreach ($periodo as $fecha) {
            $diaSemana = $fecha->format('N'); // 1 (lunes) a 7 (domingo)

            if (in_array($diaSemana, $diasTrabajo)) {
                foreach ($horariosDisponibles as $horario) {
                    $hora = \DateTime::createFromFormat('H:i', $horario);
                    $turnosGenerados[] = [
                        'fecha' => $fecha,
                        'hora' => $hora,
                        'disponible' => $this->verificarDisponibilidad($servicio, $fecha, $hora)
                    ];
                }
            }
        }

        return $turnosGenerados;
    }

    /**
     * Verificar disponibilidad específica
     */
    public function verificarDisponibilidad(Servicio $servicio, \DateTimeInterface $fecha, \DateTimeInterface $hora): bool
    {
        $turnoRepository = $this->entityManager->getRepository(Turno::class);
        
        $turnoExistente = $turnoRepository->findOneBy([
            'servicio' => $servicio,
            'fecha' => $fecha,
            'hora' => $hora,
            'estado' => 'reservado'
        ]);

        return $turnoExistente === null;
    }

    /**
     * Obtener horarios disponibles para una fecha específica
     */
    public function getHorariosDisponibles(Servicio $servicio, \DateTimeInterface $fecha): array
    {
        $diaSemana = $fecha->format('N');
        $diasTrabajo = $servicio->getDiasTrabajo();

        if (!in_array($diaSemana, $diasTrabajo)) {
            return [];
        }

        $horarios = $servicio->getHorariosDisponibles();
        $horariosDisponibles = [];

        foreach ($horarios as $horario) {
            $hora = \DateTime::createFromFormat('H:i', $horario);
            if ($this->verificarDisponibilidad($servicio, $fecha, $hora)) {
                $horariosDisponibles[] = $horario;
            }
        }

        return $horariosDisponibles;
    }
}