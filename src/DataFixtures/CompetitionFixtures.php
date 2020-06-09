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
        $streamersCount = (((count($games) - 1) * ((count($games) - 1) + 1)) / 2) - 2;
        $streamers = $this->playerRepository->findRandomized($streamersCount);
        $counter = 0;
        foreach ($games as $index => $game) {
            for ($i = 0; $i < $index; ++$i) {
                $playersPerTeam = $i % 5 + 1;
                $numberOfTeams = pow(2, $i % 4 + 1);
                $heldAtAfter = ($days = ($counter - $streamersCount) * 10) >= 0 ? "+$days days" : "$days days";
                $heldAtBefore = ($days = ($counter - $streamersCount + 1) * 10) >= 0 ? "+$days days" : "$days days";
                $this->competitionRepository->save((new Competition())
                    ->setGame($game)
                    ->setStreamer($streamers[$counter % count($streamers)])
                    ->setName('Competición #'.substr('0'.(++$counter), -2))
                    ->setHeldAt($faker->dateTimeBetween($heldAtAfter, $heldAtBefore))
                    ->setPlayersPerTeam($playersPerTeam)
                    ->setMaxPlayers($playersPerTeam * $numberOfTeams)
                    ->setLobbyName('LobbyName')
                    ->setLobbyPassword('LobbyPassword'), false);
            }
        }
        $this->competitionRepository->flush();
    }

    public function getDependencies()
    {
        return [GameFixtures::class, PlayerFixtures::class];
    }
}
