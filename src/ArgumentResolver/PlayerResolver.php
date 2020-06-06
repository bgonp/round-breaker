<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Repository\PlayerRepository;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

class PlayerResolver extends BaseResolver implements ParamConverterInterface
{
    public function __construct(PlayerRepository $playerRepository)
    {
        parent::__construct($playerRepository);
    }
}
