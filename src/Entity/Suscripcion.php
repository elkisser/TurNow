<?php

namespace App\Entity;

use App\Repository\SuscripcionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuscripcionRepository::class)]
class Suscripcion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'suscripciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\Column(length: 50)]
    private ?string $tipo = null; // 'basico', 'profesional', 'empresa'

    #[ORM\Column]
    private ?\DateTimeImmutable $fechaInicio = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fechaFin = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fechaCreacion = null;

    #[ORM\Column]
    private ?bool $activa = true;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getFechaInicio(): ?\DateTimeImmutable
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(\DateTimeImmutable $fechaInicio): static
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeImmutable
    {
        return $this->fechaFin;
    }

    public function setFechaFin(\DateTimeImmutable $fechaFin): static
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeImmutable
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeImmutable $fechaCreacion): static
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function isActiva(): ?bool
    {
        return $this->activa;
    }

    public function setActiva(bool $activa): static
    {
        $this->activa = $activa;

        return $this;
    }

    public function getDiasRestantes(): int
    {
        $now = new \DateTimeImmutable();
        if ($now > $this->fechaFin) {
            return 0;
        }
        
        $interval = $this->fechaFin->diff($now);
        return $interval->days;
    }

    public function getEstado(): string
    {
        if (!$this->activa) {
            return 'inactiva';
        }

        $diasRestantes = $this->getDiasRestantes();
        
        if ($diasRestantes === 0) {
            return 'vencida';
        } elseif ($diasRestantes <= 7) {
            return 'proxima_vencer';
        } else {
            return 'activa';
        }
    }
}