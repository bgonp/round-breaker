<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competition;
use App\Exception\NotEnoughConfirmedRegistrationsException;

class CompetitionService
{
    private TeamService $teamService;

    private RoundService $roundService;

    public function __construct(TeamService $teamService, RoundService $roundService)
    {
        $this->teamService = $teamService;
        $this->roundService = $roundService;
    }

    /**
     * @throws NotEnoughConfirmedRegistrationsException
     */
    public function randomize(Competition $competition)
    {
        $this->teamService->createFromCompetition($competition);
        $this->roundService->createFromCompetition($competition);
    }
}
