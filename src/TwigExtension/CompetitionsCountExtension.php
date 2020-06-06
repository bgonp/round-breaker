<?php

declare(strict_types=1);

namespace App\TwigExtension;

use App\Repository\CompetitionRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class CompetitionsCountExtension extends AbstractExtension implements GlobalsInterface
{
    private CompetitionRepository $competitionRepository;

    public function __construct(CompetitionRepository $competitionRepository)
    {
        $this->competitionRepository = $competitionRepository;
    }

    public function getGlobals(): array
    {
        return ['competitionsCount' => $this->competitionRepository->getTotalCount()];
    }
}
