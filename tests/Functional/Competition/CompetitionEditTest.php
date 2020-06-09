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
            $this->assertCount(1, $crawler->filter('#submit-randomize'));
            $this->assertCount(0, $crawler->filter('.match'));
            $this->assertCount(0, $crawler->filter('.team-item'));
            $this->assertCount(19, $crawler->filter('.registration-item'));
            $this->assertCount(14, $crawler->filter('.registration-item.confirmed'));
            foreach ($this->getFieldsNames() as $name) {
                $this->assertCount(1, $crawler->filter(sprintf('[name="%s"]', $name)));
            }
        }
    }

    public function testAsStreamerAndAdminNotOpen(): void
    {
        $competition = $this->getCompetition(false, false);
        for ($i = 0; $i < 2; ++$i) {
            if (0 === $i) {
                $this->login($competition->getStreamer());
            } else {
                $this->loginAsAdmin();
            }
            $crawler = $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);

            $this->assertEquals(200, $this->response()->getStatusCode());
            $this->assertCount(0, $crawler->filter('#submit-randomize'));
            $this->assertCount(2, $crawler->filter('.match'));
            $this->assertCount(0, $crawler->filter('a[data-team=""]'));
            $this->assertCount(2, $crawler->filter('.team-item'));
            $this->assertCount(20, $crawler->filter('.registration-item'));
            $this->assertCount(15, $crawler->filter('.registration-item.confirmed'));
            foreach ($this->getFieldsNames(true) as $name) {
                $this->assertCount(0, $crawler->filter(sprintf('[name="%s"]', $name)));
            }
        }
    }

    public function testSubmitEdit(): void
    {
        $competition = $this->getCompetition(true, false);
        $competitionData = [
            'name' => 'New Name',
            'description' => 'New Description',
            'game' => $this->getGame()->getId(),
            'playersPerTeam' => 3,
            'teamNum' => 8,
            'heldAt' => (new \DateTime())->format('Y-m-d\TH:i'),
        ];
        $this->login($competition->getStreamer());
        $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);
        $crawler = $this->submit('submit-edit', $competitionData);

        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertEquals(200, $this->response()->getStatusCode());
        foreach ($competitionData as $name => $value) {
            $this->assertEquals($value, $form[$name]->getValue());
        }

        $this->reloadFixtures();
    }

    public function testSubmitOpen(): void
    {
        $competition = $this->getCompetition(false, false);
        $this->login($competition->getStreamer());

        $crawler = $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);
        $form = $crawler->selectButton('submit-edit')->form();
        foreach ($this->getFieldsNames(true) as $name) {
            $this->assertFalse(isset($form[$name]));
        }
        $this->assertFalse((bool) $form['open']->getValue());

        $crawler = $this->submit('submit-edit', ['open' => true]);

        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertEquals(200, $this->response()->getStatusCode());
        foreach ($this->getFieldsNames() as $name) {
            $this->assertTrue(isset($form[$name]));
        }
        $this->assertTrue((bool) $form['open']->getValue());

        $this->reloadFixtures();
    }

    public function testSubmitOpenFinished(): void
    {
        $competition = $this->getCompetition(false, true);
        $this->login($competition->getStreamer());
        $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);
        $crawler = $this->submit('submit-edit', ['open' => true]);
        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.message.error'));
        $this->assertCount(0, $crawler->filter('#submit-randomize'));
        $this->assertFalse((bool) $form['open']->getValue());

        $this->reloadFixtures();
    }

    public function testSubmitClose(): void
    {
        $competition = $this->getCompetition(true, false);
        $this->login($competition->getStreamer());
        $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);
        $crawler = $this->submit('submit-edit', ['open' => false]);

        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertFalse((bool) $form['open']->getValue());
        $notAllowed = ['playersPerTeam', 'teamNum', 'heldAt'];
        foreach ($notAllowed as $name) {
            $this->assertFalse(isset($form[$name]));
        }

        $this->reloadFixtures();
    }

    public function testRandomize(): void
    {
        $competition = $this->getCompetition(true, false);
        $this->login($competition->getStreamer());
        $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);
        $this->submit('submit-randomize', ['competition_id' => $competition->getId()]);

        $this->assertTrue($this->response()->isRedirect(
            $this->getUrl('competition_edit', ['id' => $competition->getId()])
        ));
        $crawler = $this->followRedirect();

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertCount(0, $crawler->filter('#submit-randomize'));
        $this->assertCount(6, $crawler->filter('.match'));
        $this->assertCount(2, $crawler->filter('a[data-team=""]'));
        $this->assertCount(4, $crawler->filter('.team-item'));
        $this->assertCount(19, $crawler->filter('.registration-item'));
        $this->assertCount(14, $crawler->filter('.registration-item.confirmed'));

        $this->reloadFixtures();
    }

    public function testOpenRandomized(): void
    {
        $competition = $this->getCompetition(false, false);
        $this->login($competition->getStreamer());
        $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);
        $crawler = $this->submit('submit-edit', ['open' => true]);
        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertTrue((bool) $form['open']->getValue());
        $this->assertCount(1, $crawler->filter('#submit-randomize'));
        $this->assertCount(0, $crawler->filter('.match'));
        $this->assertCount(0, $crawler->filter('.team-item'));

        $this->reloadFixtures();
    }

    public function testOpenFinished(): void
    {
        $competition = $this->getCompetition(false, true);
        $this->login($competition->getStreamer());
        $this->request('GET', 'competition_edit', ['id' => $competition->getId()]);
        $crawler = $this->submit('submit-edit', ['open' => true]);
        $form = $crawler->selectButton('submit-edit')->form();

        $this->assertEquals(200, $this->response()->getStatusCode());
        $this->assertFalse((bool) $form['open']->getValue());
        $this->assertCount(1, $crawler->filter('.message.error'));
        $this->assertCount(0, $crawler->filter('#submit-randomize'));
        $this->assertCount(2, $crawler->filter('.match'));
        $this->assertCount(2, $crawler->filter('.team-item'));
    }

    private function getFieldsNames(bool $onlyHidden = false): array
    {
        if ($onlyHidden) {
            return ['game', 'playersPerTeam', 'teamNum', 'heldAt'];
        }
        return ['name', 'description', 'game', 'playersPerTeam', 'teamNum', 'heldAt', 'open'];
    }
}
