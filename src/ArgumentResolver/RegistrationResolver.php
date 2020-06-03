<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Repository\RegistrationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

class RegistrationResolver extends BaseResolver implements ParamConverterInterface
{
    public function __construct(RegistrationRepository $registrationRepository)
    {
        parent::__construct($registrationRepository);
    }
}