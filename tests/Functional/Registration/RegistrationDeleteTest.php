<?php

declare(strict_types=1);

namespace App\Tests\Functional\Registration;

class RegistrationDeleteTest extends RegistrationBaseTest
{
    public function testByRegistrationAsStreamer(): void
    {
        $competition = $this->getCompetition(true, false);
        $this->login($competition->getStreamer());
        $registration = $competition->getRegistrations()->get(0);
        $this->request('POST', 'registration_delete', [], ['registration_id' => $registration->getId()]);

        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_edit', ['id' => $competition->getId()])
        ));
        $this->assertFalse(
            $this->getCompetition(true, false)->getRegistrations()->contains($registration)
        );

        $this->assertFalse($this->getCompetition(true, false)->getRegistrations()->contains($registration));
        $this->reloadFixtures();
    }

    public function testByRegistrationAsAnonymous(): void
    {
        $competition = $this->getCompetition(true, false);
        $this->request('POST', 'registration_new', [], ['competition_id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_show', ['id' => $competition->getId()])
        ));
        $crawler = $this->followRedirect();

        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsNotRegistered(): void
    {
        $playerRepository = self::$container->get('App\Repository\PlayerRepository');
        $competitionRepository = self::$container->get('App\Repository\CompetitionRepository');
        $competition = $this->getCompetition(true, false);
        do {
            $player = $playerRepository->findRandomized(1)[0];
        } while (
            in_array('ROLE_ADMIN', $player->getRoles()) ||
            in_array($competition, $competitionRepository->findByPlayer($player))
        );
        $this->login($player);

        $this->request('POST', 'registration_delete', [], ['competition_id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect($this->getUrl('competition_list', ['page' => 1])));
        $crawler = $this->followRedirect();

        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsPlayer(): void
    {
        $playerRepository = self::$container->get('App\Repository\PlayerRepository');
        $competitionRepository = self::$container->get('App\Repository\CompetitionRepository');
        $competition = $this->getCompetition(true, false);
        do {
            $player = $playerRepository->findRandomized(1)[0];
        } while (
            in_array('ROLE_ADMIN', $player->getRoles()) ||
            in_array($competition, $competitionRepository->findByPlayer($player))
        );
        $this->login($player);

        $this->request('POST', 'registration_new', [], ['competition_id' => $competition->getId()]);
        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_show', ['id' => $competition->getId()])
        ));
        $this->assertTrue(in_array($competition, $competitionRepository->findByPlayer($player)));
        $this->reloadFixtures();
    }

    public function testOnNotOpen(): void
    {
        $playerRepository = self::$container->get('App\Repository\PlayerRepository');
        $competitionRepository = self::$container->get('App\Repository\CompetitionRepository');
        $competition = $this->getCompetition(false, false);
        do {
            $player = $playerRepository->findRandomized(1)[0];
        } while (
            in_array('ROLE_ADMIN', $player->getRoles()) ||
            in_array($competition, $competitionRepository->findByPlayer($player))
        );
        $this->login($player);

        $this->request('POST', 'registration_new', [], ['competition_id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_show', ['id' => $competition->getId()])
        ));
        $crawler = $this->followRedirect();

        $this->assertCount(1, $crawler->filter('.message.error'));
    }
}
