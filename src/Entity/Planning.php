<?php

namespace App\Entity;

use App\Repository\PlanningRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlanningRepository::class)
 */
class Planning
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    

    
    /**
     * @ORM\Column(type="integer")
     */
    private $semaine;

    /**
     * @ORM\ManyToOne(targetEntity=Taches::class, inversedBy="plannings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tache;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="plannings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="time")
     */
    private $dureeEst;

    
    

    public function getId(): ?int
    {
        return $this->id;
    }

   

   
    public function getSemaine(): ?int
    {
        return $this->semaine;
    }

    public function setSemaine(int $semaine): self
    {
        $this->semaine = $semaine;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDureeEst(): ?\DateTimeInterface
    {
        return $this->dureeEst;
    }

    public function setDureeEst(\DateTimeInterface $dureeEst): self
    {
        $this->dureeEst = $dureeEst;

        return $this;
    }

   
   
}
