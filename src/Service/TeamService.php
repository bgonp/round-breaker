<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Registration;
use App\Entity\Team;
use App\Repository\CompetitionRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Repository\RoundRepository;
use App\Service\CompetitionService;

class TeamService
{
	private CompetitionRepository $competitionRepository;

	private TeamRepository $teamRepository;

    private PlayerRepository $playerRepository;

    private CompetitionService $competitionService;

    private RoundRepository $roundRepository;

	public function __construct(RoundRepository $roundRepository, CompetitionRepository $competitionRepository, TeamRepository $teamRepository, PlayerRepository $playerRepository, CompetitionService $competitionService)
	{
		$this->competitionRepository = $competitionRepository;
		$this->teamRepository = $teamRepository;
        $this->playerRepository = $playerRepository;
        $this->roundRepository = $roundRepository;
        $this->competitionService = $competitionService;
    }

	public function randomize(Player $player, Competition $competition) {
	    $anyTeamPassed = $this->roundRepository->findOneBy(['bracketLevel' => 2, 'competition' => $competition]);
		if ($competition->getStreamer()->equals($player) && !$competition->getIsIndividual() && $anyTeamPassed == null) {
		    $this->teamRepository->removeTeams($competition->getTeams());
			$registrations = array_filter($competition->getRegistrations()->toArray(), function($var) {

                return $var->getIsConfirmed();
            });
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
                    if ($j==0) {
                        $team->setCaptain($registrations[$randomRegistration]->getPlayer());
                    }
                    unset($registrations[$randomRegistration]);
				}
				$teams[] = $team;
				$this->teamRepository->save($team, false);
			}
			$this->competitionService->createRounds($competition, $teams);
			$this->competitionRepository->save($competition);
		}
	}

	public function fillTeams(Player $player, Competition $competition) {
        $anyTeamPassed = $this->roundRepository->findOneBy(['bracketLevel' => 2, 'competition' => $competition]);
        if ($competition->getStreamer()->equals($player) && !$competition->getIsIndividual() && $anyTeamPassed == null) {
            $registrations = array_filter($competition->getRegistrations()->toArray(), function($var) use ($competition) {

                return $var->getIsConfirmed() && $this->teamRepository->hasTeamInCompetition($competition, $var->getPlayer()) == false;
            });
            for ($i = 0; $i < count($competition->getTeams()); $i++) {
                $team = $competition->getTeams()->toArray()[$i];
                if (count($team->getPlayers()) < $competition->getPlayersPerTeam()) {
                    $randomRegistration = array_rand($registrations);
                    $team->addPlayer($registrations[$randomRegistration]->getPlayer());
                    if ($team->getCaptain() == null) {
                        $team->setCaptain($registrations[$randomRegistration]->getPlayer());
                    }
                    unset($registrations[$randomRegistration]);
                    $this->teamRepository->save($team, false);
                }
            }
        }
        $this->competitionRepository->save($competition);
    }
}