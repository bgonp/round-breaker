<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Repository\CompetitionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

class CompetitionResolver extends BaseResolver implements ParamConverterInterface
{
    public function __construct(CompetitionRepository $competitionRepository)
    {
        parent::__construct($competitionRepository);
    }
}
