<?php

declare(strict_types=1);

namespace App\Exception;

class NotEnoughConfirmedRegistrationsException extends \RuntimeException
{
    const MESSAGE = 'Número de inscripciones confirmadas insuficiente';

    public static function create()
    {
        return new self(self::MESSAGE);
    }
}
