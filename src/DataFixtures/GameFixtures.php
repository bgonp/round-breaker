<?php

namespace App\DataFixtures;

use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GameFixtures extends Fixture
{
    private GameRepository $gameRepository;

    public function __construct(GameRepository $gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }

    public function load(ObjectManager $manager)
    {
        $games = [
            ['Hearthstone', 'Heroes of WarCraft'],
            ['Counter Strike', 'Pium pium'],
            ['League of Legends', 'Best community EVER'],
            ['Quake Champions', ''],
            ['Rocket League', 'Supersonic Acrobatic Rocket-Powered Battle-Cars'],
            ['Rainbow Six', ''],
            ['DOTA 2', ''],
            ['Starcraft 2', 'Legacy of the Void'],
        ];
        foreach ($games as $game) {
            $this->gameRepository->save((new Game())
                ->setName($game[0])
                ->setDescription($game[1]),
            false);
        }
        $this->gameRepository->flush();
    }
}
