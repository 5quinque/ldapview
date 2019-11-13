<?php

namespace App\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PeopleRepository")
 * @UniqueEntity("uid")
 */
class People
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $uid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $gecos;

    /**
     * @ORM\Column(type="integer")
     */
    private $uidNumber;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $gidNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $homeDirectory;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Netgroup", inversedBy="people")
     * @ORM\JoinTable(name="people_netgroup",
     *      joinColumns={@ORM\JoinColumn(name="people_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="netgroup_id", referencedColumnName="id")}
     *      )
     */
    private $netgroup;

    public function __construct()
    {
        $this->netgroup = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        // Using `strtolower` to always ensure uid is lowercase
        $this->uid = strtolower($uid);

        return $this;
    }

    public function getGecos(): ?string
    {
        return $this->gecos;
    }

    public function setGecos(?string $gecos): self
    {
        $this->gecos = $gecos;

        return $this;
    }

    public function getUidNumber(): ?int
    {
        return $this->uidNumber;
    }

    public function setUidNumber(int $uidNumber): self
    {
        $this->uidNumber = $uidNumber;

        return $this;
    }

    public function getGidNumber(): ?int
    {
        return $this->gidNumber;
    }

    public function setGidNumber(int $gidNumber): self
    {
        $this->gidNumber = $gidNumber;

        return $this;
    }

    public function getHomeDirectory(): ?string
    {
        return $this->homeDirectory;
    }

    public function setHomeDirectory(?string $homeDirectory): self
    {
        $this->homeDirectory = $homeDirectory;

        return $this;
    }

    /**
     * @return Collection|netgroup[]
     */
    public function getNetgroup(): Collection
    {
        return $this->netgroup;
    }

    public function addNetgroup(netgroup $netgroup): self
    {
        if (!$this->netgroup->contains($netgroup)) {
            $this->netgroup[] = $netgroup;
        }

        return $this;
    }

    public function removeNetgroup(netgroup $netgroup): self
    {
        if ($this->netgroup->contains($netgroup)) {
            $this->netgroup->removeElement($netgroup);
        }

        return $this;
    }
}
