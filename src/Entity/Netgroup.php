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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Host", inversedBy="netgroups")
     */
    private $host;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\People", mappedBy="netgroup")
     * @ORM\JoinTable(name="people_netgroup",
     *      joinColumns={@ORM\JoinColumn(name="netgroup_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="people_id", referencedColumnName="id")}
     *      )
     */
    private $people;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Netgroup", inversedBy="parent_netgroup")
     */
    private $child_netgroup;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Netgroup", mappedBy="child_netgroup")
     */
    private $parent_netgroup;

    public function __construct()
    {
        $this->host = new ArrayCollection();
        $this->people = new ArrayCollection();
        $this->child_netgroup = new ArrayCollection();
        $this->parent_netgroup = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    /**
     * @return Collection|self[]
     */
    public function getChildNetgroup(): Collection
    {
        return $this->child_netgroup;
    }

    public function addChildNetgroup(self $childNetgroup): self
    {
        if (!$this->child_netgroup->contains($childNetgroup)) {
            $this->child_netgroup[] = $childNetgroup;
        }

        return $this;
    }

    public function removeChildNetgroup(self $childNetgroup): self
    {
        if ($this->child_netgroup->contains($childNetgroup)) {
            $this->child_netgroup->removeElement($childNetgroup);
        }

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getParentNetgroup(): Collection
    {
        return $this->parent_netgroup;
    }

    public function addParentNetgroup(self $parentNetgroup): self
    {
        if (!$this->parent_netgroup->contains($parentNetgroup)) {
            $this->parent_netgroup[] = $parentNetgroup;
            $parentNetgroup->addChildNetgroup($this);
        }

        return $this;
    }

    public function removeParentNetgroup(self $parentNetgroup): self
    {
        if ($this->parent_netgroup->contains($parentNetgroup)) {
            $this->parent_netgroup->removeElement($parentNetgroup);
            $parentNetgroup->removeChildNetgroup($this);
        }

        return $this;
    }
}
