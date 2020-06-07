<?php

declare(strict_types=1);

namespace App\Tests\Functional\Competition;

use App\Entity\Competition;
use App\Entity\Game;
use App\Tests\Functional\TestBase;

abstract class CompetitionBaseTest extends TestBase
{
    protected function getCompetition(bool $open, bool $finished): Competition
    {
        $competitionRepository = self::$container->get('App\Repository\CompetitionRepository');

        return $competitionRepository->findBy(['isOpen' => $open, 'isFinished' => $finished], ['id' => 'ASC'])[0];
    }

    protected function getGame(): Game
    {
        $gameRepository = self::$container->get('App\Repository\GameRepository');

        return $gameRepository->findBy([], ['id' => 'ASC'])[0];
    }
}
