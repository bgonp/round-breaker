<?php

namespace App\DataFixtures;

use App\Entity\Competition;
use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class CompetitionFixtures extends Fixture implements DependentFixtureInterface
{
    private GameRepository $gameRepository;

    private CompetitionRepository $competitionRepository;

    private PlayerRepository $playerRepository;

    public function __construct(
        GameRepository $gameRepository,
        CompetitionRepository $competitionRepository,
        PlayerRepository $playerRepository
    ) {
        $this->gameRepository = $gameRepository;
        $this->competitionRepository = $competitionRepository;
        $this->playerRepository = $playerRepository;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create();
        $games = $this->gameRepository->findAllOrdered();
        $streamersNeeded = (count($games) * (count($games) + 1)) / 2;
        $streamers = $this->playerRepository->findRandomized($streamersNeeded);
        $counter = 0;
        foreach ($games as $index => $game) {
            for ($i = 0; $i <= $index; ++$i) {
                $playersPerTeam = 2 + $i % 4;
                $numberOfTeams = pow(2, 3 - $i);
                $heldAtAfter = ($days = ($counter - 4) * 10) >= 0 ? "+$days days" : "$days days";
                $heldAtBefore = ($days = ($counter - 3) * 10) >= 0 ? "+$days days" : "$days days";
                $this->competitionRepository->save((new Competition())
                    ->setGame($game)
                    ->setStreamer($streamers[$counter])
                    ->setName('Competición #'.substr('0'.(++$counter), -2))
                    ->setHeldAt($faker->dateTimeBetween($heldAtAfter, $heldAtBefore))
                    ->setPlayersPerTeam($playersPerTeam)
                    ->setMaxPlayers($playersPerTeam * $numberOfTeams), false);
            }
        }
        $this->competitionRepository->flush();
    }

    public function getDependencies()
    {
        return [GameFixtures::class, PlayerFixtures::class];
    }
}
