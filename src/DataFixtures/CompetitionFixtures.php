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
        $games = $this->gameRepository->findAll();
        $streamersNeeded = (count($games)*(count($games) + 1)) / 2;
        $streamers = $this->playerRepository->findRandomized($streamersNeeded);
        $counter = 0;
        foreach ($games as $index => $game) {
            for ($i = 0; $i <= $index; $i++) {
                $playersPerTeam = rand(1, 5);
                $numberOfTeams = pow(2, rand(1,4));
                $this->competitionRepository->save((new Competition())
                    ->setGame($game)
                    ->setStreamer($streamers[$counter])
                    ->setHeldAt($faker->dateTimeBetween('-30 days', '+30 days'))
                    ->setName('Competición #' . substr('0'.(++$counter), -2))
                    ->setPlayersPerTeam($playersPerTeam)
                    ->setMaxPlayers($playersPerTeam * $numberOfTeams)
                , false);
            }
        }
        $this->competitionRepository->flush();
    }

    public function getDependencies()
    {
        return [GameFixtures::class, PlayerFixtures::class];
    }
}
