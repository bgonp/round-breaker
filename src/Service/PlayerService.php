<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Team;
use App\Entity\Player;
use App\Repository\CompetitionRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;

class PlayerService
{

    /** @var PlayerRepository */
    private $playerRepository;

    public function __construct(CompetitionRepository $competitionRepository, TeamRepository $teamRepository, PlayerRepository $playerRepository)
    {
        $this->playerRepository = $playerRepository;
    }

    public function addUserToTeam(Team $team, Player $user)
    {
        $user->addTeam($team);
        $this->playerRepository->save($user);
    }
}