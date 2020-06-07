<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    protected function getPlayer(): ?Player
    {
        /** @var Player $player */
        $player = $this->getUser();

        return $player;
    }
}
