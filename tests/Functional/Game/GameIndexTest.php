<?php

declare(strict_types=1);

namespace App\Tests\Functional\Game;

class GameIndexTest extends GameBaseTest
{
    public function testAsAnonymous(): void
    {
        $crawler = $this->request('GET', 'game_list', ['page' => 1]);
        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(8, $crawler->filter('.game-item'));
    }

    public function testAsUser(): void
    {
        $this->login($this->getRandomPlayer());

        $crawler = $this->request('GET', 'game_list', ['page' => 1]);
        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(8, $crawler->filter('.game-item'));
    }

    public function testAsAdmin(): void
    {
        $this->loginAsAdmin();

        $crawler = $this->request('GET', 'game_list', ['page' => 1]);
        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(8, $crawler->filter('.game-item'));
        $this->assertCount(8, $crawler->filter('.submit-delete'));
    }
}