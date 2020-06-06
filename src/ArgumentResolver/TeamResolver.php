<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Repository\TeamRepository;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

class TeamResolver extends BaseResolver implements ParamConverterInterface
{
    public function __construct(TeamRepository $teamRepository)
    {
        parent::__construct($teamRepository);
    }
}
