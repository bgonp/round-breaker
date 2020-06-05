<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Team;
use App\Repository\CompetitionRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
use App\Repository\TeamRepository;
use App\Repository\RoundRepository;

class TeamService
{
    private CompetitionRepository $competitionRepository;

    private CompetitionService $competitionService;

	private TeamRepository $teamRepository;

    private RegistrationRepository $registrationRepository;

    private RoundRepository $roundRepository;

    private PlayerRepository $playerRepository;

	public function __construct(
	    CompetitionRepository $competitionRepository,
        CompetitionService $competitionService,
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

	public function randomize(Competition $competition): bool
    {
        $registrations = $this->registrationRepository->findConfirmedByCompetitionRandomized($competition, $competition->getMaxPlayers());
        if (count($registrations) < $competition->getMaxPlayers()) {
            $competition->setMaxPlayers($competition->getMaxPlayers() / 2);
            return $competition->getMaxPlayers() < $competition->getPlayersPerTeam() * 2 ?
                false :
                $this->randomize($competition);
        }

        if ($rounds = $competition->getRounds()) {
            $this->roundRepository->removeRounds($competition->getRounds());
        }
        $playersPerTeam = $competition->getPlayersPerTeam();
        $teams = [];
        for ($registrationIndex = 0; $registrationIndex < $competition->getMaxPlayers(); $registrationIndex++) {
            if ($registrationIndex % $playersPerTeam === 0) {
                $teams[] = (new Team())
                    ->setName('Team ' . (($registrationIndex / $playersPerTeam) + 1))
                    ->setCompetition($competition);
            }
            $team = end($teams);
            $team->addPlayer($registrations[$registrationIndex]->getPlayer());
            if (($registrationIndex + 1) % $playersPerTeam === 0) {
                $team->setCaptain($team->getPlayers()->get(rand(0, $playersPerTeam-1)));
                $this->teamRepository->save($team, false);
            }
        }
        $this->competitionRepository->save($competition->setIsOpen(false));
        $this->competitionService->createRounds($competition, $teams);
        return true;
	}

    public function replacePlayer(Team $team, Player $player): bool
    {
        $newPlayer = $this->playerRepository->findOneConfirmedNotInTeamRandomized($team->getCompetition());
        if (!$newPlayer || !$team->getPlayers()->contains($player)) {
            return false;
        }
        $this->teamRepository->save($team->removePlayer($player)->addPlayer($newPlayer));
        return true;
	}
}