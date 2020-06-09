<?php

declare(strict_types=1);

namespace App\Tests\Functional\Game;

class GameNewTest extends GameBaseTest
{
    public function testAsAnonymous(): void
    {
        $this->request('GET', 'game_new');

        $this->assertResponseRedirects($this->getUrl('game_list'));
        $crawler = $this->followRedirect();
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsUser(): void
    {
        $this->login($this->getRandomPlayer());
        $this->request('GET', 'game_new');

        $this->assertResponseRedirects($this->getUrl('game_list'));
        $crawler = $this->followRedirect();
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsAdmin(): void
    {
        $this->loginAsAdmin();
        $crawler = $this->request('GET', 'game_new');
        $form = $crawler->selectButton('submit-new')->form();

        $this->assertResponseStatusCodeSame(200);
        $this->assertTrue(isset($form['name']));
        $this->assertTrue(isset($form['description']));
    }

    public function testAsAdminSubmit(): void
    {
        $this->loginAsAdmin();
        $this->request('POST', 'game_new', [], ['name' => 'Name', 'description' => 'Description']);

        $this->assertResponseRedirects($this->getUrl('game_list'));
        $crawler = $this->followRedirect();
        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(9, $crawler->filter('.game-item'));

        $this->reloadFixtures();
    }

    public function testAsAdminSubmitRepeated(): void
    {
        $this->loginAsAdmin();
        $game = $this->getGame();
        $crawler = $this->request('POST', 'game_new', [], ['name' => $game->getName(), 'description' => 'Description']);
        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.message.error'));
    }
}
