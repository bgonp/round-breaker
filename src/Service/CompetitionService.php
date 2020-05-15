<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Registration;
use App\Entity\Team;
use App\Entity\Game;
use App\Repository\CompetitionRepository;
use App\Repository\TeamRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;

class CompetitionService
{
    /** @var CompetitionRepository */
    private $competitionRepository;

    /** @var PlayerRepository */
    private $playerRepository;

    /** @var TeamRepository */
    private $teamRepository;

    /** @var RegistrationRepository */
    private $registrationRepository;

    public function __construct(
        CompetitionRepository $competitionRepository,
        PlayerRepository $playerRepository,
        TeamRepository $teamRepository,
        RegistrationRepository $registrationRepository)
    {
        $this->competitionRepository = $competitionRepository;
        $this->playerRepository = $playerRepository;
        $this->teamRepository = $teamRepository;
        $this->registrationRepository = $registrationRepository;
    }

    public function createCompetition(
        string $name, string $description,
        Player $user, int $maxPlayers, bool $isIndividual,
        int $playersPerTeam, Game $game)
    {
        $competition = new Competition();
        $competition->setName($name);
        $competition->setDescription($description);
        $competition->setIsOpen(true);
        $competition->setIsFinished(false);
        $competition->setCreator($user);
        $competition->setMaxPlayers($maxPlayers);
        if ($isIndividual || $playersPerTeam === 1) {
            $competition->setIsIndividual(true);
            $competition->setPlayersPerTeam(1);
        } else {
            $competition->setIsIndividual(false);
            $competition->setPlayersPerTeam($playersPerTeam);
        }
        $competition->setGame($game);
        $this->competitionRepository->save($competition);
    }

    public function addPlayerToCompetition(Competition $competition, Player $player)
    {
        $registration = new Registration();
        $registration->setPlayer($player);
        $competition->addRegistration($registration);
        if ($competition->getIsIndividual()) {
            $team = new Team();
            $team->addPlayer($player);
            $team->setName($player->getUsername());
            $team->setCompetition($competition);
            $this->teamRepository->save($team);
        }
        //$competition->addTeam($team);
        $this->registrationRepository->save($registration);
        $this->competitionRepository->save($competition);
    }
}