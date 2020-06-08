<?php

declare(strict_types=1);

namespace App\Tests\Functional\Competition;

class CompetitionShowTest extends CompetitionBaseTest
{
    public function testOpen(): void
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

    public function testInProgress(): void
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

    public function testFinished(): void
    {
        $competition = $this->getCompetition(false, true);
        $crawler = $this->request('GET', 'competition_show', ['id' => $competition->getId()]);

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertEquals($competition->getStreamer()->getUsername(), $crawler->filter('.streamer a')->text());
        $this->assertCount(6, $crawler->filter('.match'));
        $this->assertCount(0, $crawler->filter('a[data-team=""]'));
        $this->assertCount(4, $crawler->filter('.team-item'));
        $this->assertCount(13, $crawler->filter('.registration-item'));
        $this->assertCount(10, $crawler->filter('.registration-item.confirmed'));
    }
}
