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
            ['Hearthstone', 'Juego de cartas coleccionables en línea centrado en el universo de Warcraft'],
            ['Counter Strike', 'Videojuegos de disparos multijugador en primera persona en los que equipos de terroristas luchan contra antiterroristas'],
            ['League of Legends', 'Juego multijugador de arena de batalla en línea y deporte electrónico desarrollado por Riot Games'],
            ['Quake Champions', 'FPS desarrollado por id Software y publicado por Bethesda Softworks. Forma parte de la serie Quake'],
            ['Rocket League', 'Supersonic Acrobatic Rocket-Powered Battle-Cars'],
            ['Rainbow Six', 'Tom Clancy\'s Rainbow Six: Siege es un videojuego de disparos en primera persona táctico multijugador'],
            ['DOTA 2', 'Pertenece al género de Arena de batalla en línea MOBA (estrategia de acción en tiempo real)'],
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
