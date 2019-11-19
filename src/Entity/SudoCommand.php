<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SudoCommandRepository")
 */
class SudoCommand
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=511)
     */
    private $command;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sudo", inversedBy="sudoCommands")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sudoGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getSudoGroup(): ?Sudo
    {
        return $this->sudoGroup;
    }

    public function setSudoGroup(?Sudo $sudoGroup): self
    {
        $this->sudoGroup = $sudoGroup;

        return $this;
    }
}
