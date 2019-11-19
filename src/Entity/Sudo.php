<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SudoRepository")
 */
class Sudo
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Host", inversedBy="sudoGroup")
     */
    private $host;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\People", inversedBy="sudoGroup")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SudoCommand", mappedBy="sudoGroup", orphanRemoval=true)
     */
    private $sudoCommands;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Netgroup", inversedBy="sudoGroup")
     */
    private $netgroup;

    public function __construct()
    {
        $this->host = new ArrayCollection();
        $this->user = new ArrayCollection();
        $this->sudoCommands = new ArrayCollection();
        $this->netgroup = new ArrayCollection();
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
     * @return Collection|Host[]
     */
    public function getHost(): Collection
    {
        return $this->host;
    }

    public function addHost(Host $host): self
    {
        if (!$this->host->contains($host)) {
            $this->host[] = $host;
        }

        return $this;
    }

    public function removeHost(Host $host): self
    {
        if ($this->host->contains($host)) {
            $this->host->removeElement($host);
        }

        return $this;
    }

    /**
     * @return Collection|People[]
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(People $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
        }

        return $this;
    }

    public function removeUser(People $user): self
    {
        if ($this->user->contains($user)) {
            $this->user->removeElement($user);
        }

        return $this;
    }

    /**
     * @return Collection|SudoCommand[]
     */
    public function getSudoCommands(): Collection
    {
        return $this->sudoCommands;
    }

    public function addSudoCommand(SudoCommand $sudoCommand): self
    {
        if (!$this->sudoCommands->contains($sudoCommand)) {
            $this->sudoCommands[] = $sudoCommand;
            $sudoCommand->setSudoGroup($this);
        }

        return $this;
    }

    public function removeSudoCommand(SudoCommand $sudoCommand): self
    {
        if ($this->sudoCommands->contains($sudoCommand)) {
            $this->sudoCommands->removeElement($sudoCommand);
            // set the owning side to null (unless already changed)
            if ($sudoCommand->getSudoGroup() === $this) {
                $sudoCommand->setSudoGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Netgroup[]
     */
    public function getNetgroup(): Collection
    {
        return $this->netgroup;
    }

    public function addNetgroup(Netgroup $netgroup): self
    {
        if (!$this->netgroup->contains($netgroup)) {
            $this->netgroup[] = $netgroup;
        }

        return $this;
    }

    public function removeNetgroup(Netgroup $netgroup): self
    {
        if ($this->netgroup->contains($netgroup)) {
            $this->netgroup->removeElement($netgroup);
        }

        return $this;
    }
}
