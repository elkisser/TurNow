<?php

namespace App\Entity;

use App\Repository\UsuarioTemporalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsuarioTemporalRepository::class)]
class UsuarioTemporal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    /**
     * @var Collection<int, Turno>
     */
    #[ORM\OneToMany(targetEntity: Turno::class, mappedBy: 'usuarioTemporal')]
    private Collection $turnos;

    public function __construct()
    {
        $this->turnos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

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
            $turno->setUsuarioTemporal($this);
        }

        return $this;
    }

    public function removeTurno(Turno $turno): static
    {
        if ($this->turnos->removeElement($turno)) {
            // set the owning side to null (unless already changed)
            if ($turno->getUsuarioTemporal() === $this) {
                $turno->setUsuarioTemporal(null);
            }
        }

        return $this;
    }
}
