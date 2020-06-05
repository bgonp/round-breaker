<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Repository\GameRepository;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

class GameResolver extends BaseResolver implements ParamConverterInterface
{
    public function __construct(GameRepository $gameRepository)
    {
        parent::__construct($gameRepository);
    }
}