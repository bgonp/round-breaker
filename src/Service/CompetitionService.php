<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Registration;
use App\Entity\Round;
use App\Entity\Team;
use App\Entity\Game;
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

    public function createCompetition(
        string $name, string $description,
        Player $user, int $maxPlayers, bool $isIndividual,
        int $playersPerTeam, Game $game, String $dateTime)
    {
        $competition = new Competition();
        $competition->setName($name);
        $competition->setDescription($description);
        $competition->setIsOpen(true);
        $competition->setIsFinished(false);
        $competition->setStreamer($user);
        $competition->setMaxPlayers($maxPlayers);
        if ($isIndividual || $playersPerTeam === 1) {
            $competition->setIsIndividual(true);
            $competition->setPlayersPerTeam(1);
        } else {
            $competition->setIsIndividual(false);
            $competition->setPlayersPerTeam($playersPerTeam);
        }
        $competition->setGame($game);
        $competition->setHeldAt(new \DateTime($dateTime));
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

    public function createRound(Competition $competition, $bestOf, $bracketLevel, $bracketOrder) {
        $round = new Round();
        $round->setCompetition($competition);
        $round->setBestOf($bestOf);
        $round->setBracketLevel($bracketLevel);
        $round->setBracketOrder($bracketOrder);
        $this->roundRepository->save($round);
        return $round;
    }

    public function createRounds(Competition $competition, Array $teams) {
        $numRounds = count($teams)/2;
        $numLevels = log(count($teams), 2);
        for ($i = 0; $i < $numLevels; $i++) {
            for ($j = 0; $j < $numRounds; $j++) {
                $round = $this->createRound($competition, 3, $i+1, $j+1);
                if ($i == 0) {
                    $round->addTeam($teams[$j*2]);
                    $round->addTeam($teams[$j*2+1]);
                }
                $this->roundRepository->save($round);
            }
            $numRounds = $numRounds/2;
        }
    }

    /** @return Round|null Affected round */
    public function advanceTeam(Team $team, Round $round): ?Round {
        $nextRound = $this->getNextRound($round);
        if ($nextRound && $nextRound->getWinner()) {
            throw new \InvalidArgumentException('Can\'t modify round if next round already finished');
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
            throw new \InvalidArgumentException('Can\'t modify round if next round already finished');
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
        $bracketOrder = $round->getBracketOrder();
        // TODO: Eliminar esta llamada a base de datos, sisterRound no hace falta
        $sisterRound = $this->roundRepository->findOneBy([
            'bracketLevel' => $round->getBracketLevel(),
            'bracketOrder' => $bracketOrder + 1
        ]);
        $nextRound = null;
        if ($bracketOrder > 1 || $sisterRound) {
            if ($bracketOrder % 2 != 0) {
                $bracketOrder++;
            }
            $bracketOrder = $bracketOrder/2;
            $nextRound = $this->roundRepository->findOneBy([
                'bracketLevel' => $round->getBracketLevel() + 1,
                'bracketOrder' => $bracketOrder
            ]);
        }
        return $nextRound;
    }
}