<?php

namespace App\Entity;

use App\Repository\TacheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TacheRepository::class)
 */
class Taches
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string")
     */
    private $duree_est;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $duree_rel;

    /**
     * @ORM\ManyToOne(targetEntity=FamilleTache::class, inversedBy="taches")
     */
    private $famille;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fini;

    /**
     * @ORM\OneToMany(targetEntity=Intervention::class, mappedBy="tache", orphanRemoval=true)
     * 
     */
    private $interventions;

    

    /**
     * @ORM\OneToMany(targetEntity=Planning::class, mappedBy="tache", orphanRemoval=true)
     */
    private $plannings;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->plannings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDureeEst(): ?String
    {
        return $this->duree_est;
    }

    public function setDureeEst(?String $duree_est): self
    {
        $this->duree_est = $duree_est;

        return $this;
    }

    public function getDureeRel(): string
    {
        return $this->duree_rel;
    }

    public function setDureeRel(string $duree_rel): self
    {
        $this->duree_rel = $duree_rel;

        return $this;
    }

    public function getFamille(): ?FamilleTache
    {
        return $this->famille;
    }

    public function setFamille(?FamilleTache $famille): self
    {
        $this->famille = $famille;

        return $this;
    }

    public function getFini(): ?bool
    {
        return $this->fini;
    }

    public function setFini(bool $fini): self
    {
        $this->fini = $fini;

        return $this;
    }

     /**
     * @return Collection|Intervention[]
     */
    public function getIntervention(): Collection
    {
        return $this->interventions;
    }

    public function addInter(Intervention $inter): self
    {
        if (!$this->interventions->contains($inter)) {
            $this->interventions[] = $inter;
            $inter->setFamille($this);
        }

        return $this;
    }

    public function removeInter(Intervention $inter): self
    {
        if ($this->interventions->removeElement($inter)) {
            // set the owning side to null (unless already changed)
            if ($inter->getTache() === $this) {
                $inter->setTache(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection|Planning[]
     */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): self
    {
        if (!$this->plannings->contains($planning)) {
            $this->plannings[] = $planning;
            $planning->setTache($this);
        }

        return $this;
    }

    public function removePlanning(Planning $planning): self
    {
        if ($this->plannings->removeElement($planning)) {
            // set the owning side to null (unless already changed)
            if ($planning->getTache() === $this) {
                $planning->setTache(null);
            }
        }

        return $this;
    }
}
