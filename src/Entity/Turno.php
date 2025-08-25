<?php

namespace App\Entity;

use App\Repository\TurnoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TurnoRepository::class)]
class Turno
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $hora = null;

    #[ORM\Column(length: 20)]
    private ?string $estado = 'disponible';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $usuario_email = null; // ← Campo para el email del usuario

    #[ORM\ManyToOne(inversedBy: 'turnos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Servicio $servicio = null;

    #[ORM\ManyToOne(inversedBy: 'turnos')]
    private ?UsuarioTemporal $usuarioTemporal = null;

    public function __construct()
    {
        $this->estado = 'disponible';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): ?\DateTime
    {
        return $this->fecha;
    }

    public function setFecha(\DateTime $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getHora(): ?\DateTime
    {
        return $this->hora;
    }

    public function setHora(\DateTime $hora): static
    {
        $this->hora = $hora;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getUsuarioEmail(): ?string
    {
        return $this->usuario_email;
    }

    public function setUsuarioEmail(?string $usuario_email): static
    {
        $this->usuario_email = $usuario_email;

        return $this;
    }

    public function getServicio(): ?Servicio
    {
        return $this->servicio;
    }

    public function setServicio(?Servicio $servicio): static
    {
        $this->servicio = $servicio;

        return $this;
    }

    public function getUsuarioTemporal(): ?UsuarioTemporal
    {
        return $this->usuarioTemporal;
    }

    public function setUsuarioTemporal(?UsuarioTemporal $usuarioTemporal): static
    {
        $this->usuarioTemporal = $usuarioTemporal;

        return $this;
    }
}
