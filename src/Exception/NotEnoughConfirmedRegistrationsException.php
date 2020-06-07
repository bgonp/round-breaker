<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class NotEnoughConfirmedRegistrationsException extends BadRequestHttpException
{
    const MESSAGE = 'Número de inscripciones confirmadas insuficiente';

    public static function create()
    {
        return new self(self::MESSAGE);
    }
}
