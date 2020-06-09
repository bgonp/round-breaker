<?php

declare(strict_types=1);

namespace App\Tests\Functional\Game;

class GameShowTest extends GameBaseTest
{
    public function testAsAnonymous(): void
    {
        $game = $this->getGame();
        $crawler = $this->request('GET', 'game_show', ['id' => $game->getId(), 'page' => 1]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(2, $crawler->filter('.competition-item'));
    }
}
