<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 */
class Team extends Base
{
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Competition", mappedBy="teams")
     */
    private $competitions;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lobbyName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lobbyPassword;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rank;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Round", mappedBy="teams")
     */
    private $rounds;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Player", inversedBy="teams")
     */
    private $players;

    public function __construct()
    {
        parent::__construct();
        $this->rounds = new ArrayCollection();
        $this->players = new ArrayCollection();
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

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): self
    {
        $this->rank = $rank;

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

    /**
     * @return Collection|Round[]
     */
    public function getRounds(): Collection
    {
        return $this->rounds;
    }

    public function addRound(Round $round): self
    {
        if (!$this->rounds->contains($round)) {
            $this->rounds[] = $round;
            $round->addTeam($this);
        }

        return $this;
    }

    public function removeRound(Round $round): self
    {
        if ($this->rounds->contains($round)) {
            $this->rounds->removeElement($round);
            $round->removeTeam($this);
        }

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
        }

        return $this;
    }

    public function removePlayer(Player $player): self
    {
        if ($this->players->contains($player)) {
            $this->players->removeElement($player);
        }

        return $this;
    }

    /**
     * @return Collection|Competition[]
     */
    public function getCompetitions(): Collection
    {
        return $this->competitions;
    }

    public function addCompetition(Competition $competition): self
    {
        if (!$this->competitions->contains($competition)) {
            $this->competitions[] = $competition;
            $competition->addTeam($this);
        }

        return $this;
    }

    public function removeCompetition(Competition $competition): self
    {
        if ($this->competitions->contains($competition)) {
            $this->competitions->removeElement($competition);
            $competition->addTeam($this);
        }

        return $this;
    }
}
