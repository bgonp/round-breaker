<?php

declare(strict_types=1);

namespace App\Tests\Functional\Competition;

class CompetitionEditTest extends CompetitionBaseTest
{
    public function testAsAnonymous(): void
    {
        $competition = $this->getCompetition(true, false);
        $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_show', ['id' => $competition->getId()])
        ));
    }

    public function testAsUser(): void
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

    public function testAsStreamerAndAdmin(): void
    {
        $competition = $this->getCompetition(true, false);
        for ($i = 0; $i < 2; ++$i) {
            if (0 === $i) {
                $this->login($competition->getStreamer());
            } else {
                $this->loginAsAdmin();
            }
            $crawler = $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);

            $this->assertEquals(200, $this->response()->getStatusCode());
            $this->assertCount(0, $crawler->filter('.match'));
            $this->assertCount(0, $crawler->filter('.team-item'));
            $this->assertCount(21, $crawler->filter('.registration-item'));
            $this->assertCount(18, $crawler->filter('.registration-item.confirmed'));
        }
    }

    public function testSubmitOpen(): void
    {
        $competition = $this->getCompetition(true, false);
        $competitionData = [
            'name' => 'New Name',
            'description' => 'New Description',
            'game' => $this->getGame()->getId(),
            'playersPerTeam' => 3,
            'teamNum' => 8,
            'heldAt' => (new \DateTime())->format('Y-m-d\TH:i'),
            'open' => false,
        ];
        $this->login($competition->getStreamer());
        $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);
        $crawler = $this->submit('submit-edit', $competitionData);
        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertEquals(200, $this->response()->getStatusCode());
        $editables = ['name', 'description', 'open'];
        foreach ($competitionData as $name => $value) {
            if (in_array($name, $editables, true)) {
                $this->assertEquals($value, $form[$name]->getValue());
            } else {
                $this->assertFalse(isset($form[$name]));
            }
        }

        $this->reloadFixtures();
    }
}
