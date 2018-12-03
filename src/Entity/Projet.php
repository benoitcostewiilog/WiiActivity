<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjetRepository")
 */
class Projet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"activite"})
     */
    private $charge_estime;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activite", mappedBy="projet")
     */
    private $activites;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"activite"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"activite"})
     */
    private $progression;

    public function __construct()
    {
        $this->activites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChargeEstime(): ?int
    {
        return $this->charge_estime;
    }

    public function setChargeEstime(int $charge_estime): self
    {
        $this->charge_estime = $charge_estime;

        return $this;
    }

    /**
     * @return Collection|Activite[]
     */
    public function getActivites(): Collection
    {
        return $this->activites;
    }

    public function addActivite(Activite $activite): self
    {
        if (!$this->activites->contains($activite)) {
            $this->activites[] = $activite;
            $activite->setProjet($this);
        }

        return $this;
    }

    public function removeActivite(Activite $activite): self
    {
        if ($this->activites->contains($activite)) {
            $this->activites->removeElement($activite);
            // set the owning side to null (unless already changed)
            if ($activite->getProjet() === $this) {
                $activite->setProjet(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getProgression(): ?int
    {
        return $this->progression;
    }

    public function setProgression(?int $progression): self
    {
        $this->progression = $progression;

        return $this;
    }


}
