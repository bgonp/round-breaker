<?php

declare(strict_types=1);

namespace App\TwigExtension;

use App\Entity\Competition;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class GetTeamsNumberExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [new TwigFilter('teamsNumber', [$this, 'getTeamsNumber'])];
    }

    public function getTeamsNumber(Competition $competition)
    {
        return $competition->getMaxPlayers() / $competition->getPlayersPerTeam();
    }
}