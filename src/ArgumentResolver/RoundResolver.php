<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Repository\RoundRepository;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

class RoundResolver extends BaseResolver implements ParamConverterInterface
{
    public function __construct(RoundRepository $roundRepository)
    {
        parent::__construct($roundRepository);
    }
}
