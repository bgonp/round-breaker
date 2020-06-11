<?php

declare(strict_types=1);

namespace App\Tests\Functional\Player;

class PlayerShowTest extends PlayerBaseTest
{
    public function testAsAnonymous(): void
    {
        $player = $this->getRandomPlayer();
        $crawler = $this->request('GET', 'player_show', ['id' => $player->getId()]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount($player->getCompetitionsCreated()->count(), $crawler->filter('.competition-item'));
        $this->assertCount($player->getTeams()->count(), $crawler->filter('.team-item'));
        $this->assertCount($player->getRegistrations()->count(), $crawler->filter('.registration-item'));
    }
}
