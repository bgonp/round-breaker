<?php

declare(strict_types=1);

namespace App\Tests\Functional\Game;

use App\Entity\Game;
use App\Entity\Player;
use App\Tests\Functional\TestBase;

abstract class GameBaseTest extends TestBase
{
    protected function getGame(bool $hasCompetitions = true): ?Game
    {
        /** @var Game[] $games */
        $games = $this->getRepository('Game')->findBy([], ['id' => 'ASC']);
        foreach ($games as $game) {
            if ($game->getCompetitions()->count() xor !$hasCompetitions) {
                return $game;
            }
        }

        return null;
    }

    protected function getRandomPlayer(): Player
    {
        do {
            $player = $this->getRepository('Player')->findRandomized(1)[0];
        } while (in_array('ROLE_ADMIN', $player->getRoles()));

        return $player;
    }
}
