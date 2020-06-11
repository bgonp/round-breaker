<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

class ApiSetRoundWinnerTest extends ApiBaseTest
{
    public function testAsNotStreamer(): void
    {
        $competition = $this->getCompetition(false, false);
        do {
            $player = $this->getRandomPlayer();
        } while ($competition->getStreamer()->equals($player));
        $this->login($player);
        $team = $competition->getTeams()[0];
        $round = $team->getRounds()[0];

        $this->request('POST', 'api_winner', ['round_id' => $round->getId(), 'team_id' => $team->getId()]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAsStreamer(): void
    {
        $competition = $this->getCompetition(false, false);
        $this->login($competition->getStreamer());
        $team = $competition->getTeams()[0];
        $round = $team->getRounds()[0];

        $this->request('POST', 'api_winner', ['round_id' => $round->getId(), 'team_id' => $team->getId()]);
        $this->assertResponseStatusCodeSame(200);

        $this->reloadFixtures();
    }
}
