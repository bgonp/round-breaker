<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Team;
use App\Exception\NotEnoughConfirmedRegistrationsException;
use App\Repository\CompetitionRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
use App\Repository\RoundRepository;
use App\Repository\TeamRepository;
use Faker;

class TeamService
{
    private CompetitionRepository $competitionRepository;

    private RoundService $competitionService;

    private TeamRepository $teamRepository;

    private RegistrationRepository $registrationRepository;

    private RoundRepository $roundRepository;

    private PlayerRepository $playerRepository;

    public function __construct(
        CompetitionRepository $competitionRepository,
        RoundService $competitionService,
        TeamRepository $teamRepository,
        RegistrationRepository $registrationRepository,
        RoundRepository $roundRepository,
        PlayerRepository $playerRepository
    ) {
        $this->competitionRepository = $competitionRepository;
        $this->competitionService = $competitionService;
        $this->teamRepository = $teamRepository;
        $this->registrationRepository = $registrationRepository;
        $this->roundRepository = $roundRepository;
        $this->playerRepository = $playerRepository;
    }

    /**
     * @throws NotEnoughConfirmedRegistrationsException
     */
    public function createFromCompetition(Competition $competition): void
    {
        $registrations = $this->registrationRepository->findConfirmedByCompetitionRandomized($competition, $competition->getMaxPlayers());
        if (count($registrations) < $competition->getMaxPlayers()) {
            $competition->setMaxPlayers($competition->getMaxPlayers() / 2);
            if ($competition->getMaxPlayers() < $competition->getPlayersPerTeam() * 2) {
                throw NotEnoughConfirmedRegistrationsException::create();
            }
            $this->createFromCompetition($competition);
        }

        $playersPerTeam = $competition->getPlayersPerTeam();
        $faker = Faker\Factory::create();
        for ($registrationIndex = 0; $registrationIndex < $competition->getMaxPlayers(); ++$registrationIndex) {
            if (0 === $registrationIndex % $playersPerTeam) {
                $team = (new Team())->setName($faker->streetName);
                $competition->addTeam($team);
            }
            $team->addPlayer($registrations[$registrationIndex]->getPlayer());
            if (0 === ($registrationIndex + 1) % $playersPerTeam) {
                $team->setCaptain($team->getPlayers()->get(rand(0, $playersPerTeam - 1)));
                $this->teamRepository->save($team, false);
            }
        }
        $this->competitionRepository->save($competition->setIsOpen(false));
    }

    public function replacePlayer(Team $team, Player $player): void
    {
        $newPlayer = $this->playerRepository->findOneConfirmedNotInTeamRandomized($team->getCompetition());
        if (!$newPlayer || !$team->getPlayers()->contains($player)) {
            throw NotEnoughConfirmedRegistrationsException::create();
        }
        $registration = $this->registrationRepository->findOneByPlayerAndCompetition($player, $team->getCompetition());
        $this->registrationRepository->save($registration->setIsConfirmed(false), false);
        $this->teamRepository->save($team->removePlayer($player)->addPlayer($newPlayer));
    }
}
