<?php

declare(strict_types=1);

namespace App\Tests\Functional\Competition;

use DateTime;

class CompetitionNewTest extends CompetitionBaseTest
{
    public function testAsAnonymous()
    {
        $this->request('GET', 'competition_new');

        $this->assertTrue($this->response()->isRedirect($this->getUrl('main')));
    }

    public function testSubmit(): void
    {
        $datetime = new DateTime();
        $competitionData = [
            'name' => 'Test Name',
            'description' => 'Test Description',
            'game' => $this->getGame()->getId(),
            'playersPerTeam' => 3,
            'teamNum' => 8,
            'heldAtDate' => $datetime->format('Y-m-d'),
            'heldAtTime' => $datetime->format('H:i'),
        ];
        $this->login();
        $this->request('GET', 'competition_new');
        $this->submit('submit-new', $competitionData);

        $this->assertTrue($this->response()->isRedirect());
        $crawler = $this->followRedirect();

        $this->reloadFixtures();

        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertEquals(200, $this->response()->getStatusCode());
        foreach ($competitionData as $name => $value) {
            $this->assertEquals($value, $form[$name]->getValue());
        }
    }

    public function testSubmitError(): void
    {
        $competitionData = [
            'name' => '',
            'description' => 'Test Description',
            'playersPerTeam' => 3,
            'teamNum' => 8,
        ];
        $this->login();
        $this->request('GET', 'competition_new');
        $crawler = $this->submit('submit-new', $competitionData);

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertEquals('Crear torneo | Round Breaker', $crawler->filter('title')->text());
        $this->assertCount(1, $crawler->filter('.message.error'));
    }
}
