<?php

declare(strict_types=1);

namespace App\Tests\Functional\Registration;

class RegistrationNewTest extends RegistrationBaseTest
{
    public function testAsAnonymous(): void
    {
        $competition = $this->getCompetition(true, false);
        $this->request('POST', 'registration_new', ['competition_id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_show', ['id' => $competition->getId()])
        ));
        $crawler = $this->followRedirect();

        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsRegistered(): void
    {
        $competition = $this->getCompetition(true, false);
        do {
            $player = $this->getRandomPlayer();
        } while (!in_array($competition, $this->getCompetitionByPlayer($player)));
        $this->login($player);

        $this->request('POST', 'registration_new', ['competition_id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_show', ['id' => $competition->getId()])
        ));
        $crawler = $this->followRedirect();

        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsPlayer(): void
    {
        $competition = $this->getCompetition(true, false);
        do {
            $player = $this->getRandomPlayer();
        } while (in_array($competition, $this->getCompetitionByPlayer($player)));
        $this->login($player);

        $this->request('POST', 'registration_new', ['competition_id' => $competition->getId()]);
        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_show', ['id' => $competition->getId()])
        ));
        $this->assertTrue(in_array($competition, $this->getCompetitionByPlayer($player)));
    }

    public function testOnNotOpen(): void
    {
        $competition = $this->getCompetition(false, false);
        do {
            $player = $this->getRandomPlayer();
        } while (in_array($competition, $this->getCompetitionByPlayer($player)));
        $this->login($player);

        $this->request('POST', 'registration_new', ['competition_id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_show', ['id' => $competition->getId()])
        ));
        $crawler = $this->followRedirect();

        $this->assertCount(1, $crawler->filter('.message.error'));
    }
}
