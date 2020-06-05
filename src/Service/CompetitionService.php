<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Registration;
use App\Entity\Round;
use App\Entity\Team;
use App\Entity\Game;
use App\Exception\CannotModifyWinnerException;
use App\Repository\CompetitionRepository;
use App\Repository\TeamRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
use App\Repository\RoundRepository;

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

    /** @var RoundRepository */
    private $roundRepository;

    public function __construct(
        CompetitionRepository $competitionRepository,
        PlayerRepository $playerRepository,
        TeamRepository $teamRepository,
        RegistrationRepository $registrationRepository,
        RoundRepository $roundRepository)
    {
        $this->competitionRepository = $competitionRepository;
        $this->playerRepository = $playerRepository;
        $this->teamRepository = $teamRepository;
        $this->registrationRepository = $registrationRepository;
        $this->roundRepository = $roundRepository;
    }

    public function createRounds(Competition $competition, array $teams) {
        $numRounds = count($teams)/2;
        $numLevels = log(count($teams), 2);
        for ($i = 0; $i < $numLevels; $i++) {
            for ($j = 0; $j < $numRounds; $j++) {
                $round = (new Round())
                    ->setCompetition($competition)
                    ->setBestOf(3)
                    ->setBracketLevel($i+1)
                    ->setBracketOrder($j+1);
                if ($i == 0) {
                    $round->addTeam($teams[$j*2]);
                    $round->addTeam($teams[$j*2+1]);
                }
                $this->roundRepository->save($round, false);
            }
            $numRounds = $numRounds/2;
        }
        $this->roundRepository->flush();
    }

    /** @return Round|null Affected round */
    public function advanceTeam(Team $team, Round $round): ?Round {
        $nextRound = $this->getNextRound($round);
        if ($nextRound && $nextRound->getWinner()) {
            throw CannotModifyWinnerException::create();
        }
        if ($round->getWinner() && !$round->getWinner()->equals($round)) {
            $this->undoAdvanceTeam($round->getWinner(), $round, false);
        }
        $round->setWinner($team);
        $this->roundRepository->save($round, false);
        if ($nextRound) {
            $nextRound->addTeam($team);
            $this->roundRepository->save($nextRound, false);
        }
        $this->roundRepository->flush();
        return $nextRound;
    }

    /** @return Round|null Affected round */
    public function undoAdvanceTeam(Team $team, Round $round, bool $flush = true): ?Round
    {
        $nextRound = $this->getNextRound($round);
        if ($nextRound && $nextRound->getWinner()) {
            throw CannotModifyWinnerException::create();
        }
        $round->setWinner(null);
        $this->roundRepository->save($round, false);
        if ($nextRound) {
            $nextRound->removeTeam($team);
            $this->roundRepository->save($nextRound, false);
        }
        if ($flush) $this->roundRepository->flush();
        return $nextRound;
    }

    private function getNextRound(Round $round): ?Round
    {
        return $this->roundRepository->findOneBy([
            'competition' => $round->getCompetition(),
            'bracketLevel' => $round->getBracketLevel() + 1,
            'bracketOrder' => ceil($round->getBracketOrder() / 2)
        ]);
    }
}