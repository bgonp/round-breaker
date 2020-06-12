<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompetitionRepository")
 */
class Competition extends Base
{
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isOpen = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isFinished = false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $lobbyName = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $lobbyPassword = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $heldAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game", inversedBy="competitions")
     */
    private Game $game;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Registration", mappedBy="competition")
     * @ORM\OrderBy({"isConfirmed" = "DESC", "updatedAt" = "DESC"})
     */
    private Collection $registrations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Team", mappedBy="competition")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private Collection $teams;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Round", mappedBy="competition")
     * @ORM\OrderBy({"bracketLevel" = "ASC", "bracketOrder" = "ASC"})
     */
    private Collection $rounds;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="competitionsCreated")
     */
    private Player $streamer;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min = 1, max = 5)
     */
    private int $playersPerTeam;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min = 2, max = 64)
     */
    private int $maxPlayers;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $twitchBotName = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $twitchBotToken = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $twitchChannel = null;

    public function __construct()
    {
        parent::__construct();
        $this->registrations = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->rounds = new ArrayCollection();
    }

    public function getIsOpen(): bool
    {
        return $this->isOpen;
    }

    public function setIsOpen(bool $isOpen): self
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    public function getIsFinished(): bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(bool $isFinished): self
    {
        $this->isFinished = $isFinished;

        return $this;
    }

    public function getName(): string
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

    public function getLobbyName(): ?string
    {
        return $this->lobbyName;
    }

    public function setLobbyName(string $lobbyName): self
    {
        $this->lobbyName = $lobbyName;

        return $this;
    }

    public function getLobbyPassword(): ?string
    {
        return $this->lobbyPassword;
    }

    public function setLobbyPassword(string $lobbyPassword): self
    {
        $this->lobbyPassword = $lobbyPassword;

        return $this;
    }

    public function getHeldAt(): ?\DateTimeInterface
    {
        return $this->heldAt;
    }

    public function setHeldAt(\DateTimeInterface $heldAt): self
    {
        $this->heldAt = $heldAt;

        return $this;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getStreamer(): Player
    {
        return $this->streamer;
    }

    public function setStreamer(Player $streamer): self
    {
        $this->streamer = $streamer;

        return $this;
    }

    public function getMaxPlayers(): int
    {
        return $this->maxPlayers;
    }

    public function setMaxPlayers(int $maxPlayers): self
    {
        $this->maxPlayers = $maxPlayers;

        return $this;
    }

    public function getPlayersPerTeam(): int
    {
        return $this->playersPerTeam;
    }

    public function setPlayersPerTeam(int $playersPerTeam): self
    {
        $this->playersPerTeam = $playersPerTeam;

        return $this;
    }

    /**
     * @return Collection|Registration[]
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
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
            $team->setCompetition($this);
        }

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
            $round->setCompetition($this);
        }

        return $this;
    }

    public function getTwitchBotName(): ?string
    {
        return $this->twitchBotName;
    }

    public function setTwitchBotName(?string $twitchBotName): self
    {
        $this->twitchBotName = $twitchBotName;

        return $this;
    }

    public function getTwitchBotToken(): ?string
    {
        return $this->twitchBotToken;
    }

    public function setTwitchBotToken(?string $twitchBotToken): self
    {
        $this->twitchBotToken = $twitchBotToken;

        return $this;
    }

    public function getTwitchChannel(): ?string
    {
        return $this->twitchChannel;
    }

    public function setTwitchChannel(?string $twitchChannel): self
    {
        $this->twitchChannel = $twitchChannel;

        return $this;
    }
}
