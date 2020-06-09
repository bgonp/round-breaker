<?php

declare(strict_types=1);

namespace App\Tests\Functional\Game;

class GameDeleteTest extends GameBaseTest
{
    public function testAsAnonymous(): void
    {
        $this->request('POST', 'game_delete', [], ['game_id' => $this->getGame()->getId()]);

        $this->assertResponseRedirects($this->getUrl('game_list'));
        $crawler = $this->followRedirect();
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsUser(): void
    {
        $this->login($this->getRandomPlayer());
        $this->request('POST', 'game_delete', [], ['game_id' => $this->getGame()->getId()]);

        $this->assertResponseRedirects($this->getUrl('game_list'));
        $crawler = $this->followRedirect();
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsAdminWithCompetitions(): void
    {
        $this->loginAsAdmin();
        $this->request('POST', 'game_delete', [], ['game_id' => $this->getGame(true)->getId()]);

        $this->assertResponseRedirects($this->getUrl('game_list'));
        $crawler = $this->followRedirect();
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsAdminWithoutCompetitions(): void
    {
        $this->loginAsAdmin();
        $this->request('POST', 'game_delete', [], ['game_id' => $this->getGame(false)->getId()]);

        $this->assertResponseRedirects($this->getUrl('game_list'));
        $crawler = $this->followRedirect();
        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(7, $crawler->filter('.game-item'));

        $this->reloadFixtures();
    }
}