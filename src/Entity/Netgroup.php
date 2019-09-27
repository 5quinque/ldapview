<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NetgroupRepository")
 */
class Netgroup
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Host", inversedBy="netgroups")
     */
    private $host;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\People", mappedBy="netgroup")
     */
    private $people;

    public function __construct()
    {
        $this->host = new ArrayCollection();
        $this->people = new ArrayCollection();
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
     * @return Collection|host[]
     */
    public function getHost(): Collection
    {
        return $this->host;
    }

    public function addHost(host $host): self
    {
        if (!$this->host->contains($host)) {
            $this->host[] = $host;
        }

        return $this;
    }

    public function removeHost(host $host): self
    {
        if ($this->host->contains($host)) {
            $this->host->removeElement($host);
        }

        return $this;
    }

    /**
     * @return Collection|People[]
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(People $person): self
    {
        if (!$this->people->contains($person)) {
            $this->people[] = $person;
            $person->addNetgroup($this);
        }

        return $this;
    }

    public function removePerson(People $person): self
    {
        if ($this->people->contains($person)) {
            $this->people->removeElement($person);
            $person->removeNetgroup($this);
        }

        return $this;
    }
}
