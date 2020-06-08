<?php

declare(strict_types=1);

namespace App\Tests\Functional\Competition;

class CompetitionDeleteTest extends CompetitionBaseTest
{
    public function testAsAnonymous(): void
    {
        $competition = $this->getCompetition(true, false);
        $competition_id = $competition->getId();
        $this->request('POST', 'competition_delete', ['competition_id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect($this->getUrl('competition_list', ['page' => 1])));
        $crawler = $this->followRedirect();

        $this->assertEquals($competition_id, $this->getCompetition(true, false)->getId());
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsUser(): void
    {
        $competition = $this->getCompetition(true, false);
        $competition_id = $competition->getId();
        $playerRepository = self::$container->get('App\Repository\PlayerRepository');
        do {
            $player = $playerRepository->findRandomized(1)[0];
        } while ($competition->getStreamer()->equals($player) || in_array('ROLE_ADMIN', $player->getRoles()));
        $this->login($player);
        $this->request('POST', 'competition_delete', ['competition_id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect($this->getUrl('competition_list', ['page' => 1])));
        $crawler = $this->followRedirect();

        $this->assertEquals($competition_id, $this->getCompetition(true, false)->getId());
        $this->assertCount(1, $crawler->filter('.message.error'));
    }

    public function testAsStreamer(): void
    {
        $competition = $this->getCompetition(true, false);
        $competition_id = $competition->getId();
        $this->login($competition->getStreamer());
        $this->request('POST', 'competition_delete', ['competition_id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect($this->getUrl('competition_list', ['page' => 1])));
        $crawler = $this->followRedirect();

        $this->assertNotEquals($competition_id, $this->getCompetition(true, false)->getId());
        $this->assertCount(0, $crawler->filter('.message.error'));
    }
}
