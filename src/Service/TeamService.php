<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Team;
use App\Repository\CompetitionRepository;
use App\Repository\TeamRepository;

class TeamService
{
	/** @var CompetitionRepository */
	private $competitionRepository;

	/** @var TeamRepository */
	private $teamRepository;

	public function __construct(CompetitionRepository $competitionRepository, TeamRepository $teamRepository)
	{
		$this->competitionRepository = $competitionRepository;
		$this->teamRepository = $teamRepository;
	}

	public function randomize(Player $player, Competition $competition) {
		$competition = $this->competitionRepository->findOneBy(['name' => $competition]);
		if ($competition->getIsOpen() && $competition->getCreator()->equals($player) && !$competition->getIsIndividual()) {
			$registrations = $competition->getRegistrations()->toArray();
			$teamNum = floor(count($registrations)/$competition->getPlayersPerTeam());
			$teamNum = $teamNum > $competition->getMaxPlayers()/$competition->getPlayersPerTeam() ? $competition->getMaxPlayers()/$competition->getPlayersPerTeam() : $teamNum;
			if ($teamNum % 2 != 0) {
				$teamNum = $teamNum-1;
			}
			$registrations = array_slice($registrations, 0, $competition->getPlayersPerTeam()*$teamNum);
			for($i = 0; $i < $teamNum; $i++) {
				$team = new Team();
				$team->setName("Team " . ($i+1));
				$team->setCompetition($competition);
				for($j = 0; $j < $competition->getPlayersPerTeam(); $j++) {
					$randomRegistration = array_rand($registrations);
					$team->addPlayer($registrations[$randomRegistration]->getPlayer());
					unset($registrations[$randomRegistration]);
				}
				$this->teamRepository->save($team, false);
			}
			$competition->setIsOpen(false);
			$this->competitionRepository->save($competition);
		}
	}
}