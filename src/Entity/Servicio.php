<?php

namespace App\Entity;

use App\Repository\ServicioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServicioRepository::class)]
class Servicio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $banner_url = null;

    #[ORM\Column(type: Types::JSON)]
    private array $dias_trabajo = [];

    #[ORM\Column(type: Types::JSON)]
    private array $horarios_disponibles = [];

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'servicios')] // ← Cambiado de Administrador a Usuario
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $administrador = null;

    /**
     * @var Collection<int, Turno>
     */
    #[ORM\OneToMany(targetEntity: Turno::class, mappedBy: 'servicio')]
    private Collection $turnos;

    public function __construct()
    {
        $this->turnos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getBannerUrl(): ?string
    {
        return $this->banner_url;
    }

    public function setBannerUrl(?string $banner_url): static
    {
        $this->banner_url = $banner_url;

        return $this;
    }

    public function getDiasTrabajo(): array
    {
        return $this->dias_trabajo;
    }

    public function setDiasTrabajo(array $dias_trabajo): static
    {
        $this->dias_trabajo = $dias_trabajo;

        return $this;
    }

    public function getHorariosDisponibles(): array
    {
        return $this->horarios_disponibles;
    }

    public function setHorariosDisponibles(array $horarios_disponibles): static
    {
        // Asegurar que siempre sea un array
        $this->horarios_disponibles = is_array($horarios_disponibles) ? $horarios_disponibles : [];
        return $this;
    }

    public function getAdministrador(): ?Usuario // ← Cambiado el tipo de retorno
    {
        return $this->administrador;
    }

    public function setAdministrador(?Usuario $administrador): static // ← Cambiado el tipo de parámetro
    {
        $this->administrador = $administrador;

        return $this;
    }

    /**
     * @return Collection<int, Turno>
     */
    public function getTurnos(): Collection
    {
        return $this->turnos;
    }

    public function addTurno(Turno $turno): static
    {
        if (!$this->turnos->contains($turno)) {
            $this->turnos->add($turno);
            $turno->setServicio($this);
        }

        return $this;
    }

    public function removeTurno(Turno $turno): static
    {
        if ($this->turnos->removeElement($turno)) {
            // set the owning side to null (unless already changed)
            if ($turno->getServicio() === $this) {
                $turno->setServicio(null);
            }
        }

        return $this;
    }
}
