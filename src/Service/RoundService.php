<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Entity\Round;
use App\Entity\Team;
use App\Exception\CannotModifyWinnerException;
use App\Repository\RoundRepository;
use App\Repository\TeamRepository;

class RoundService
{
    private RoundRepository $roundRepository;

    private TeamRepository $teamRepository;

    public function __construct(RoundRepository $roundRepository, TeamRepository $teamRepository)
    {
        $this->roundRepository = $roundRepository;
        $this->teamRepository = $teamRepository;
    }

    public function createFromCompetition(Competition $competition)
    {
        $teams = $competition->getTeams();
        $numRounds = $teams->count() / 2;
        $numLevels = log($teams->count(), 2);
        for ($i = 0; $i < $numLevels; ++$i) {
            for ($j = 0; $j < $numRounds; ++$j) {
                $round = (new Round())
                    ->setBracketLevel($i + 1)
                    ->setBracketOrder($j + 1);
                $competition->addRound($round);
                if (0 == $i) {
                    $round->addTeam($teams->get($j * 2));
                    $round->addTeam($teams->get($j * 2 + 1));
                }
                $this->roundRepository->save($round, false);
            }
            $numRounds = $numRounds / 2;
        }
        $this->roundRepository->flush();
    }

    /** @return Round|null Affected round */
    public function advanceTeam(Team $team, Round $round): ?Round
    {
        $nextRound = $this->getNextRound($round);
        if ($nextRound && $nextRound->getWinner()) {
            throw CannotModifyWinnerException::create();
        }
        if ($round->getWinner() && !$round->getWinner()->equals($round)) {
            $this->undoAdvanceTeam($round->getWinner(), $round, false);
        }
        $round->setWinner($team);
        if (!$nextRound) {
            $team->setRanking(1);
        }
        $this->setLosersRanking($round);
        $this->roundRepository->save($round, false);
        if ($nextRound) {
            $nextRound->addTeam($team);
            $this->roundRepository->save($nextRound, false);
        }
        $this->roundRepository->flush();

        if ($nextRound) {
            // Call repository again in order to obtain teams ordered
            $this->roundRepository->refresh($nextRound);
        }

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
        foreach ($round->getTeams() as $roundTeam) {
            $this->teamRepository->save($roundTeam->setRanking(null), false);
        }
        $this->roundRepository->save($round, false);
        if ($nextRound) {
            $nextRound->removeTeam($team);
            $this->roundRepository->save($nextRound, false);
        }
        if ($flush) {
            $this->roundRepository->flush();
        }

        return $nextRound;
    }

    private function getNextRound(Round $round): ?Round
    {
        return $this->roundRepository->findOneBy([
            'competition' => $round->getCompetition(),
            'bracketLevel' => $round->getBracketLevel() + 1,
            'bracketOrder' => (int) ceil($round->getBracketOrder() / 2),
        ]);
    }

    private function setLosersRanking(Round $round): void
    {
        $teamsPerRound = $round->getTeams()->count();
        $competitionLevels = (int) log($round->getCompetition()->getTeams()->count(), 2);
        $losersRanking = max(2, ($competitionLevels - $round->getBracketLevel()) * $teamsPerRound + 1);
        foreach ($round->getTeams() as $opponent) {
            if (!$opponent->equals($round->getWinner())) {
                $opponent->setRanking($losersRanking);
            }
        }
    }
}
