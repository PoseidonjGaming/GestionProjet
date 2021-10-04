<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InterventionRepository::class)
 */
class Intervention
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
   
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $pb;

    /**
     * @ORM\ManyToOne(targetEntity=Taches::class, inversedBy="interventions")
     * @ORM\JoinColumn(nullable=true)
     */
    private $tache;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $Le_user;

    /**
     * @ORM\Column(type="date")
     */
    private $Date;

    /**
     * @ORM\Column(type="time")
     */
    private $duree;

   
   
    public function getId(): ?int
    {
        return $this->id;
    }


    public function getPb(): ?string
    {
        return $this->pb;
    }

    public function setPb(?string $pb): self
    {
        $this->pb = $pb;

        return $this;
    }

    public function getTache(): ?Taches
    {
        return $this->tache;
    }

    public function setTache(?Taches $tache): self
    {
        $this->tache = $tache;

        return $this;
    }

    public function getLeUser(): ?user
    {
        return $this->Le_user;
    }

    public function setLeUser(?user $Le_user): self
    {
        $this->Le_user = $Le_user;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->Date;
    }

    public function setDate(\DateTimeInterface $Date): self
    {
        $this->Date = $Date;

        return $this;
    }

    public function getDuree(): ?\DateTimeInterface
    {
        return $this->duree;
    }

    public function setDuree(\DateTimeInterface $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

   

   
   
}
