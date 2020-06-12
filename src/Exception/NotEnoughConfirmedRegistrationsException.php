<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class NotEnoughConfirmedRegistrationsException extends BadRequestException
{
    const MESSAGE = 'Número de inscripciones confirmadas insuficiente.';

    public static function create()
    {
        return new self(self::MESSAGE);
    }
}
