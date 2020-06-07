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
    private Competition $competition;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Team", inversedBy="rounds")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private Collection $teams;

    /**
     * @ORM\Column(type="integer")
     */
    private int $bracketLevel = 1;

    /**
     * @ORM\Column(type="integer")
     */
    private int $bracketOrder = 1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Team $winner = null;

    public function __construct()
    {
        parent::__construct();
        $this->teams = new ArrayCollection();
    }

    public function getCompetition(): Competition
    {
        return $this->competition;
    }

    public function setCompetition(Competition $competition): self
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

    public function getBracketLevel(): int
    {
        return $this->bracketLevel;
    }

    public function setBracketLevel(int $bracketLevel): self
    {
        $this->bracketLevel = $bracketLevel;

        return $this;
    }

    public function getBracketOrder(): int
    {
        return $this->bracketOrder;
    }

    public function setBracketOrder(int $bracketOrder): self
    {
        $this->bracketOrder = $bracketOrder;

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
