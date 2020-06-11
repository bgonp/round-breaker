<?php

declare(strict_types=1);

namespace App\Tests\Functional\Player;

class PlayerEditTest extends PlayerBaseTest
{
    public function testAsAnonymous(): void
    {
        $player = $this->getRandomPlayer();
        $this->request('GET', 'player_edit', ['id' => $player->getId()]);

        $this->assertResponseRedirects($this->getUrl('player_show', ['id' => $player->getId()]));
        $crawler = $this->followRedirect();
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsUser(): void
    {
        $loggedPlayer = $this->getRandomPlayer();
        $this->login($loggedPlayer);
        do {
            $player = $this->getRandomPlayer();
        } while ($player->equals($loggedPlayer));
        $this->request('GET', 'player_edit', ['id' => $player->getId()]);

        $this->assertResponseRedirects($this->getUrl('player_show', ['id' => $player->getId()]));
        $crawler = $this->followRedirect();
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsUserSelf(): void
    {
        $player = $this->getRandomPlayer();
        $this->login($player);
        $this->request('GET', 'player_edit', ['id' => $player->getId()]);

        $this->assertResponseRedirects($this->getUrl('profile'));
    }

    public function testAsAdmin(): void
    {
        $player = $this->getRandomPlayer();
        $this->loginAsAdmin();
        $crawler = $this->request('GET', 'player_edit', ['id' => $player->getId()]);
        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals($player->getUsername(), $form['username']->getValue());
        $this->assertEquals('', $form['password']->getValue());
        $this->assertEquals($player->getEmail(), $form['email']->getValue());
        $this->assertEquals($player->getTwitchName(), $form['twitch_name']->getValue());

        $form['username']->setValue('New username');
        $form['email']->setValue('newmail@newmail.com');
        $form['twitch_name']->setValue('new_twitchname');
        $crawler = $this->submitForm($form);
        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertEquals('New username', $form['username']->getValue());
        $this->assertEquals('newmail@newmail.com', $form['email']->getValue());
        $this->assertEquals('new_twitchname', $form['twitch_name']->getValue());

        $this->reloadFixtures();
    }
}
