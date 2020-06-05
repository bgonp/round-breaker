<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RegistrationRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="unique_player_competition", columns={"player_id", "competition_id"})})
 */
class Registration extends Base
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="registrations", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition", inversedBy="registrations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $competition;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isConfirmed;

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): self
    {
        $this->competition = $competition;

        return $this;
    }

    public function getIsConfirmed(): ?bool
    {
        return $this->isConfirmed;
    }

    public function setIsConfirmed(bool $isConfirmed): self
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }
}
