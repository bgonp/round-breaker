<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Exception\NotEnoughConfirmedRegistrationsException;
use App\Repository\RoundRepository;
use App\Repository\TeamRepository;

class CompetitionService
{
    private RoundRepository $roundRepository;

    private TeamService $teamService;

    private RoundService $roundService;

    private TeamRepository $teamRepository;

    public function __construct(
        RoundRepository $roundRepository,
        TeamRepository $teamRepository,
        RoundService $roundService,
        TeamService $teamService
    ) {
        $this->roundRepository = $roundRepository;
        $this->teamRepository = $teamRepository;
        $this->roundService = $roundService;
        $this->teamService = $teamService;
    }

    /**
     * @throws NotEnoughConfirmedRegistrationsException
     */
    public function randomize(Competition $competition)
    {
        $this->teamRepository->removeFromCompetition($competition);
        $this->roundRepository->removeFromCompetition($competition);
        $this->teamService->createFromCompetition($competition);
        $this->roundService->createFromCompetition($competition);
    }
}
