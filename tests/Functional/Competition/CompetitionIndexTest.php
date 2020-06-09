<?php

declare(strict_types=1);

namespace App\Tests\Functional\Competition;

class CompetitionIndexTest extends CompetitionBaseTest
{
    public function testAsAnonymous(): void
    {
        $crawler = $this->request('GET', 'competition_list', ['page' => 1]);

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(16, $crawler->filter('.competition-item'));
        $this->assertCount(13, $crawler->filter('.competition-item.finished'));
        $this->assertCount(2, $crawler->filter('.competition-item.open'));
    }
}
