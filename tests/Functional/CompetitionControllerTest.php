<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Competition;

class CompetitionControllerTest extends TestBase
{
    public function testIndex(): void
    {
        $crawler = $this->request('GET', 'competition_list');

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(6, $crawler->filter('.competition-item'));
        $this->assertCount(3, $crawler->filter('.competition-item.finished'));
        $this->assertCount(2, $crawler->filter('.competition-item.open'));
    }

    public function testShowOpen(): void
    {
        $competition = $this->getCompetition(true, false);
        $crawler = $this->request('GET', 'competition_show', ['id' => $competition->getId()]);

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertEquals($competition->getStreamer()->getUsername(), $crawler->filter('.streamer a')->text());
        $this->assertCount(0, $crawler->filter('.match'));
        $this->assertCount(0, $crawler->filter('.team-item'));
        $this->assertCount(21, $crawler->filter('.registration-item'));
        $this->assertCount(18, $crawler->filter('.registration-item.confirmed'));
    }

    public function testShowInProgress(): void
    {
        $competition = $this->getCompetition(false, false);
        $crawler = $this->request('GET', 'competition_show', ['id' => $competition->getId()]);

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertEquals($competition->getStreamer()->getUsername(), $crawler->filter('.streamer a')->text());
        $this->assertCount(14, $crawler->filter('.match'));
        $this->assertCount(6, $crawler->filter('a[data-team=""]'));
        $this->assertCount(8, $crawler->filter('.team-item'));
        $this->assertCount(22, $crawler->filter('.registration-item'));
        $this->assertCount(19, $crawler->filter('.registration-item.confirmed'));
    }

    public function testShowFinished(): void
    {
        $competition = $this->getCompetition(false, true);
        $crawler = $this->request('GET', 'competition_show', ['id' => $competition->getId()]);

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertEquals($competition->getStreamer()->getUsername(), $crawler->filter('.streamer a')->text());
        $this->assertCount(14, $crawler->filter('.match'));
        $this->assertCount(0, $crawler->filter('a[data-team=""]'));
        $this->assertCount(8, $crawler->filter('.team-item'));
        $this->assertCount(13, $crawler->filter('.registration-item'));
        $this->assertCount(10, $crawler->filter('.registration-item.confirmed'));
    }

    public function testEditAsAnonymous(): void
    {
        $competition = $this->getCompetition(true, false);
        $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_show', ['id' => $competition->getId()])
        ));
    }

    public function testEditAsUser(): void
    {
        $playerRepository = self::$container->get('App\Repository\PlayerRepository');
        $competition = $this->getCompetition(true, false);
        do {
            $player = $playerRepository->findRandomized(1)[0];
        } while ($competition->getStreamer()->equals($player) || in_array('ROLE_ADMIN', $player->getRoles()));
        $this->login($player);
        $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_show', ['id' => $competition->getId()])
        ));
    }

    public function testEditAsStreamer(): void
    {
        $competition = $this->getCompetition(true, false);
        $this->login($competition->getStreamer());
        $crawler = $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(0, $crawler->filter('.match'));
        $this->assertCount(0, $crawler->filter('.team-item'));
        $this->assertCount(21, $crawler->filter('.registration-item'));
        $this->assertCount(18, $crawler->filter('.registration-item.confirmed'));
    }

    public function testEditAsAdmin(): void
    {
        $this->loginAsAdmin();
        $competition = $this->getCompetition(true, false);
        $crawler = $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(0, $crawler->filter('.match'));
        $this->assertCount(0, $crawler->filter('.team-item'));
        $this->assertCount(21, $crawler->filter('.registration-item'));
        $this->assertCount(18, $crawler->filter('.registration-item.confirmed'));
    }

    private function getCompetition(bool $open, bool $finished): Competition
    {
        $competitionRepository = self::$container->get('App\Repository\CompetitionRepository');

        return $competitionRepository->findBy(['isOpen' => $open, 'isFinished' => $finished], ['id' => 'ASC'])[0];
    }
}
