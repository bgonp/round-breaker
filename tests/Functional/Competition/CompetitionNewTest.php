<?php

declare(strict_types=1);

namespace App\Tests\Functional\Competition;

class CompetitionNewTest extends CompetitionBaseTest
{
    public function testAsAnonymous()
    {
        $this->request('GET', 'competition_new');

        $this->assertTrue($this->response()->isRedirect($this->getUrl('main')));
    }

    public function testSubmit(): void
    {
        $competitionData = [
            'name' => 'Test Name',
            'description' => 'Test Description',
            'game' => $this->getGame()->getId(),
            'playersPerTeam' => 3,
            'teamNum' => 8,
            'heldAt' => (new \DateTime())->format('Y-m-d\TH:i'),
        ];
        $this->login();
        $this->request('GET', 'competition_new');
        $this->submit('submit-new', $competitionData);

        $this->assertTrue($this->response()->isRedirect());

        $crawler = $this->followRedirect();
        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertEquals(200, $this->response()->getStatusCode());
        foreach ($competitionData as $name => $value) {
            $this->assertEquals($value, $form[$name]->getValue());
        }

        $this->reloadFixtures();
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
        $this->assertCount(1, $crawler->filter('.message.error'));
    }
}
