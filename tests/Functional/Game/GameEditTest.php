<?php

declare(strict_types=1);

namespace App\Tests\Functional\Game;

class GameEditTest extends GameBaseTest
{
    public function testAsAnonymous(): void
    {
        $this->request('GET', 'game_edit', ['id' => $this->getGame()->getId()]);

        $this->assertResponseRedirects($this->getUrl('main'));
        $crawler = $this->followRedirect();
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsUser(): void
    {
        $this->login($this->getRandomPlayer());
        $this->request('GET', 'game_edit', ['id' => $this->getGame()->getId()]);

        $this->assertResponseRedirects($this->getUrl('main'));
        $crawler = $this->followRedirect();
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsAdmin(): void
    {
        $this->loginAsAdmin();
        $game = $this->getGame();
        $crawler = $this->request('GET', 'game_edit', ['id' => $game->getId()]);
        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals($game->getName(), $form['name']->getValue());
        $this->assertEquals($game->getDescription(), $form['description']->getValue());
    }

    public function testSubmit(): void
    {
        $this->loginAsAdmin();
        $this->request('GET', 'game_edit', ['id' => $this->getGame()->getId()]);
        $crawler = $this->submit('submit-edit', ['name' => 'New name', 'description' => 'New description']);
        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('New name', $form['name']->getValue());
        $this->assertEquals('New description', $form['description']->getValue());
    }
}