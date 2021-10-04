<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjetRepository::class)
 */
class Projet
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
     * @ORM\Column(type="date")
     */
    private $datecrea;

    /**
     * @ORM\Column(type="integer")
     */
    private $avancee;

    /**
     * @ORM\ManyToOne(targetEntity=Compte::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $createur_id;

     /**
     * @ORM\Column(type="boolean")
     */
    private $archive;

    /**
     * @ORM\OneToMany(targetEntity=FamilleTache::class, mappedBy="id_projet", orphanRemoval=true)
     */
    private $familles;

    /**
     * @ORM\Column(type="date")
     */
    private $Date_livraison;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="projet")
     * @ORM\JoinColumn(nullable=true)
     */
    private $client;

  

    public function __construct()
    {
        $this->familles = new ArrayCollection();
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

    public function getDatecrea(): ?\DateTimeInterface
    {
        return $this->datecrea;
    }

    public function setDatecrea(\DateTimeInterface $datecrea): self
    {
        $this->datecrea = $datecrea;

        return $this;
    }

    public function getAvancee(): ?int
    {
        return $this->avancee;
    }

    public function setAvancee(int $avancee): self
    {
        $this->avancee = $avancee;

        return $this;
    }

    public function getCreateurId(): ?Compte
    {
        return $this->createur_id;
    }

    public function setCreateurId(?Compte $createur_id): self
    {
        $this->createur_id = $createur_id;

        return $this;
    }

    public function getArchive(): ?bool
    {
        return $this->archive;
    }

    public function setArchive(bool $archive): self
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * @return Collection|FamilleTache[]
     */
    public function getFamilles(): Collection
    {
        return $this->familles;
    }

    public function addFamille(FamilleTache $famille): self
    {
        if (!$this->familles->contains($famille)) {
            $this->familles[] = $famille;
            $famille->setIdProjet($this);
        }

        return $this;
    }

    public function removeFamille(FamilleTache $famille): self
    {
        if ($this->familles->removeElement($famille)) {
            // set the owning side to null (unless already changed)
            if ($famille->getIdProjet() === $this) {
                $famille->setIdProjet(null);
            }
        }

        return $this;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->Date_livraison;
    }

    public function setDateLivraison(\DateTimeInterface $Date_livraison): self
    {
        $this->Date_livraison = $Date_livraison;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    
}
