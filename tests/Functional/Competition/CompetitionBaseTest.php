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
        return static::getRepository('Competition')->findBy(['isOpen' => $open, 'isFinished' => $finished], ['id' => 'ASC'])[0];
    }

    protected function getGame(): Game
    {
        return static::getRepository('Game')->findBy([], ['id' => 'ASC'])[0];
    }
}
