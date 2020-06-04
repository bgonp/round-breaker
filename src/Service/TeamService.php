<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Entity\Team;
use App\Repository\CompetitionRepository;
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

	public function __construct(
	    CompetitionRepository $competitionRepository,
        CompetitionService $competitionService,
        TeamRepository $teamRepository,
        RegistrationRepository $registrationRepository,
        RoundRepository $roundRepository
    ) {
        $this->competitionRepository = $competitionRepository;
        $this->competitionService = $competitionService;
		$this->teamRepository = $teamRepository;
        $this->registrationRepository = $registrationRepository;
        $this->roundRepository = $roundRepository;
    }

	public function randomize(Competition $competition): bool
    {
        $registrations = $this->registrationRepository->findConfirmedByCompetitionRandomized($competition, $competition->getMaxPlayers());
        if (count($registrations) < $competition->getMaxPlayers()) {
            $competition->setMaxPlayers($competition->getMaxPlayers() / 2);
            return $competition->getMaxPlayers() < 2 * $competition->getPlayersPerTeam() ?
                false :
                $this->randomize($competition);
        }

        $this->roundRepository->removeRounds($competition->getRounds());
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

        // TODO: Lucas
        /*$registrations = array_filter($competition->getRegistrations()->toArray(), function($var) {
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
        $competition->setIsOpen(false);
        $this->competitionService->createRounds($competition, $teams);
        $this->competitionRepository->save($competition);*/
	}

	public function fillTeams(Competition $competition) {
	    $registrations = $this->registrationRepository->findRandomConfirmedNotInTeam($competition);
	    foreach ($competition->getTeams() as $team) {
	        while ($team->getPlayers()->count() < $competition->getPlayersPerTeam()) {
	            $newPlayer = array_pop($registrations);
	            $team->addPlayer($newPlayer);
	            $this->teamRepository->save($team);
            }
            if (!$team->getCaptain()) {
                $team->setCaptain($team->getPlayers()->get(0));
                $this->teamRepository->save($team);
            }
        }
	    $this->teamRepository->flush();

        /*$registrations = array_filter($competition->getRegistrations()->toArray(), function($var) use ($competition) {
            return $var->getIsConfirmed() && !$this->teamRepository->findOneByPlayerAndCompetition($var->getPlayer(), $competition);
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
        $this->teamRepository->flush();*/
    }
}