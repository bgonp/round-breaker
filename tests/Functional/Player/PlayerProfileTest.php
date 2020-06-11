<?php

declare(strict_types=1);

namespace App\Tests\Functional\Player;

class PlayerProfileTest extends PlayerBaseTest
{
    public function testAsAnonymous(): void
    {
        $this->request('GET', 'profile');
        $this->assertResponseRedirects($this->getUrl('main'));

        $crawler = $this->followRedirect();
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsUser(): void
    {
        $player = $this->getRandomPlayer();
        $this->login($player);
        $crawler = $this->request('GET', 'profile');
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
    }
}
