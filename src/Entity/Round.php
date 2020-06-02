<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoundRepository")
 */
class Round extends Base
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition", inversedBy="rounds")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $competition;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Team", inversedBy="rounds")
     */
    private $teams;

    /**
     * @ORM\Column(type="integer")
     */
    private $bracketLevel;

    /**
     * @ORM\Column(type="integer")
     */
    private $bracketOrder;

    /**
     * @ORM\Column(type="integer")
     */
    private $bestOf;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lobbyName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lobbyPassword;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $winner;

    public function __construct()
    {
        parent::__construct();
        $this->teams = new ArrayCollection();
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

    /**
     * @return Collection|Team[]
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
        }

        return $this;
    }

    public function getBracketLevel(): ?int
    {
        return $this->bracketLevel;
    }

    public function setBracketLevel(int $bracketLevel): self
    {
        $this->bracketLevel = $bracketLevel;

        return $this;
    }

    public function getBracketOrder(): ?int
    {
        return $this->bracketOrder;
    }

    public function setBracketOrder(int $bracketOrder): self
    {
        $this->bracketOrder = $bracketOrder;

        return $this;
    }

    public function getBestOf(): ?int
    {
        return $this->bestOf;
    }

    public function setBestOf(int $bestOf): self
    {
        $this->bestOf = $bestOf;

        return $this;
    }

    public function getLobbyName(): ?string
    {
        return $this->lobbyName;
    }

    public function setLobbyName(?string $lobbyName): self
    {
        $this->lobbyName = $lobbyName;

        return $this;
    }

    public function getLobbyPassword(): ?string
    {
        return $this->lobbyPassword;
    }

    public function setLobbyPassword(?string $lobbyPassword): self
    {
        $this->lobbyPassword = $lobbyPassword;

        return $this;
    }

    public function getWinner(): ?Team
    {
        return $this->winner;
    }

    public function setWinner(?Team $winner): self
    {
        $this->winner = $winner;

        return $this;
    }
}
