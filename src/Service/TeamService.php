<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Team;
use App\Repository\CompetitionRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;

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

	public function randomize(Player $player, Competition $competition) {
		if ($competition->getIsOpen() && $competition->getCreator()->equals($player) && !$competition->getIsIndividual()) {
			$registrations = $competition->getRegistrations()->toArray();
			$teamNum = floor(count($registrations)/$competition->getPlayersPerTeam());
			$teamNum = $teamNum > $competition->getMaxPlayers()/$competition->getPlayersPerTeam() ? $competition->getMaxPlayers()/$competition->getPlayersPerTeam() : $teamNum;
			if ($teamNum % 2 != 0) {
				$teamNum = $teamNum-1;
			}
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