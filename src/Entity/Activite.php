<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActiviteRepository")
 */
class Activite
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"activite"})
     */
    private $date;

    /**
     * @ORM\Column(type="float")
     * @Groups({"activite"})
     */
    private $temps;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"activite"})
     */
    private $tache;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Utilisateur", inversedBy="activites")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"activite"})
     */
    private $utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Projet", inversedBy="activites")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"activite"})
     */
    private $projet;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="activite")
     * @Groups({"activite"})
     */
    private $site;

    public function getId() : ? int
    {
        return $this->id;
    }

    public function getDate() : ? \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date) : self
    {
        $this->date = $date;

        return $this;
    }

    public function getTemps() : ? float
    {
        return $this->temps;
    }

    public function setTemps(float $temps) : self
    {
        $this->temps = $temps;

        return $this;
    }

    public function getTache() : ? string
    {
        return $this->tache;
    }

    public function setTache(string $tache) : self
    {
        $this->tache = $tache;

        return $this;
    }

    public function getUtilisateur() : ? Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(? Utilisateur $utilisateur) : self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getProjet() : ? Projet
    {
        return $this->projet;
    }

    public function setProjet(? Projet $projet) : self
    {
        $this->projet = $projet;

        return $this;
    }

    public function __toString()
    {
        return $this->tache;
    }

    public function getSite() : ? Site
    {
        return $this->site;
    }

    public function setSite(? Site $site) : self
    {
        $this->site = $site;

        return $this;
    }

}
