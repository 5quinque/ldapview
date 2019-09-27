<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HostRepository")
 */
class Host
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
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Netgroup", mappedBy="host")
     */
    private $netgroups;

    public function __construct()
    {
        $this->netgroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|Netgroup[]
     */
    public function getNetgroups(): Collection
    {
        return $this->netgroups;
    }

    public function addNetgroup(Netgroup $netgroup): self
    {
        if (!$this->netgroups->contains($netgroup)) {
            $this->netgroups[] = $netgroup;
            $netgroup->addHost($this);
        }

        return $this;
    }

    public function removeNetgroup(Netgroup $netgroup): self
    {
        if ($this->netgroups->contains($netgroup)) {
            $this->netgroups->removeElement($netgroup);
            $netgroup->removeHost($this);
        }

        return $this;
    }
}
