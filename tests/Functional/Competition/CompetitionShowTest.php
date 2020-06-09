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
        $this->assertCount(19, $crawler->filter('.registration-item'));
        $this->assertCount(14, $crawler->filter('.registration-item.confirmed'));
    }

    public function testInProgress(): void
    {
        $competition = $this->getCompetition(false, false);
        $crawler = $this->request('GET', 'competition_show', ['id' => $competition->getId()]);

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertEquals($competition->getStreamer()->getUsername(), $crawler->filter('.streamer a')->text());
        $this->assertCount(2, $crawler->filter('.match'));
        $this->assertCount(0, $crawler->filter('a[data-team=""]'));
        $this->assertCount(2, $crawler->filter('.team-item'));
        $this->assertCount(20, $crawler->filter('.registration-item'));
        $this->assertCount(15, $crawler->filter('.registration-item.confirmed'));
    }

    public function testFinished(): void
    {
        $competition = $this->getCompetition(false, true);
        $crawler = $this->request('GET', 'competition_show', ['id' => $competition->getId()]);

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertEquals($competition->getStreamer()->getUsername(), $crawler->filter('.streamer a')->text());
        $this->assertCount(2, $crawler->filter('.match'));
        $this->assertCount(0, $crawler->filter('a[data-team=""]'));
        $this->assertCount(2, $crawler->filter('.team-item'));
        $this->assertCount(15, $crawler->filter('.registration-item'));
        $this->assertCount(10, $crawler->filter('.registration-item.confirmed'));
    }
}
