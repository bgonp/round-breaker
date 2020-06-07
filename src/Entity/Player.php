<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlayerRepository")
 */
class Player extends Base implements UserInterface
{
    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private string $username;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private ?string $plainPassword = null;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $twitchName;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Registration", mappedBy="player")
     */
    private Collection $registrations;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Team", mappedBy="players")
     */
    private Collection $teams;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Competition", mappedBy="streamer")
     */
    private Collection $competitionsCreated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Team", mappedBy="captain")
     */
    private Collection $teamsCaptained;

    public function __construct()
    {
        parent::__construct();
        $this->registrations = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->competitionsCreated = new ArrayCollection();
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getTwitchName(): ?string
    {
        return $this->twitchName;
    }

    public function setTwitchName(string $twitchName): self
    {
        $this->twitchName = $twitchName;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|Registration[]
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): self
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations[] = $registration;
            $registration->setPlayer($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): self
    {
        if ($this->registrations->contains($registration)) {
            $this->registrations->removeElement($registration);
            // set the owning side to null (unless already changed)
            if ($registration->getPlayer() === $this) {
                $registration->setPlayer(null);
            }
        }

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
            $team->addPlayer($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
            $team->removePlayer($this);
        }

        return $this;
    }

    /**
     * @return Collection|Competition[]
     */
    public function getCompetitionsCreated(): Collection
    {
        return $this->competitionsCreated;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeamsCaptained(): Collection
    {
        return $this->teamsCaptained;
    }
}
