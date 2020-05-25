<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Round;
use App\Entity\Team;
use App\Repository\CompetitionRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Service\CompetitionService;

class TeamService
{
	/** @var CompetitionRepository */
	private $competitionRepository;

	/** @var TeamRepository */
	private $teamRepository;

    /** @var PlayerRepository */
    private $playerRepository;

	public function __construct(CompetitionRepository $competitionRepository, TeamRepository $teamRepository, PlayerRepository $playerRepository)
	{
		$this->competitionRepository = $competitionRepository;
		$this->teamRepository = $teamRepository;
        $this->playerRepository = $playerRepository;
	}

	public function createTeam(string $name) {
        $team = new Team();
        $team->setName($name);
        $user = $this->getUser()->getUsername();
        $user = $this->playerRepository->findOneBy(['username' => $user]);
        $user->addTeam($team);
        $this->teamRepository->save($team);
        $this->playerRepository->save($user);
    }

	public function randomize(Player $player, Competition $competition, CompetitionService $competitionService) {
		if ($competition->getIsOpen() && $competition->getCreator()->equals($player) && !$competition->getIsIndividual()) {
			$registrations = $competition->getRegistrations()->toArray();
			$teamNum = pow(2, intval(log(floor(count($registrations)/$competition->getPlayersPerTeam()), 2)));
			$maxTeamNum = $competition->getMaxPlayers()/$competition->getPlayersPerTeam();
			if ($teamNum == 1) $teamNum = 0;
			if ($teamNum > $maxTeamNum) $teamNum = $maxTeamNum;
			$teams = [];
			for($i = 0; $i < $teamNum; $i++) {
				$team = new Team();
				$team->setName("Team " . ($i+1));
				$team->setCompetition($competition);
				for($j = 0; $j < $competition->getPlayersPerTeam(); $j++) {
					$randomRegistration = array_rand($registrations);
					$team->addPlayer($registrations[$randomRegistration]->getPlayer());
					unset($registrations[$randomRegistration]);
				}
				$teams[] = $team;
				$this->teamRepository->save($team, false);
			}
			$competitionService->createRounds($competition, $teams);
			$competition->setIsOpen(false);
			$this->competitionRepository->save($competition);
		}
	}
}