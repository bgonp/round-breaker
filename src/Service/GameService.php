<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Repository\GameRepository;

class GameService
{
    /** @var GameRepository */
    private $gameRepository;

    public function __construct(GameRepository $gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }

    public function createGame(string $name, string  $description) {
        $game = new Game();
        $game->setName($name);
        $game->setDescription($description);
        $this->gameRepository->save($game);
    }
}