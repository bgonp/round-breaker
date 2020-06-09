<?php

declare(strict_types=1);

namespace App\Tests\Functional\Competition;

use App\Entity\Competition;
use App\Entity\Game;
use App\Entity\Player;
use App\Tests\Functional\TestBase;

abstract class CompetitionBaseTest extends TestBase
{
    protected function getCompetition(bool $open, bool $finished): Competition
    {
        return $this->getRepository('Competition')->findBy(['isOpen' => $open, 'isFinished' => $finished], ['id' => 'ASC'])[0];
    }

    protected function getGame(): Game
    {
        return $this->getRepository('Game')->findBy([], ['id' => 'ASC'])[0];
    }

    protected function getRandomPlayer(): Player
    {
        do {
            $player = $this->getRepository('Player')->findRandomized(1)[0];
        } while (in_array('ROLE_ADMIN', $player->getRoles()));

        return $player;
    }
}
