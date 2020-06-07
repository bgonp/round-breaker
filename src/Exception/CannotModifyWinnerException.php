<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CannotModifyWinnerException extends BadRequestException
{
    const MESSAGE = 'No se puede modificar una ronda si la siguiente ya ha finalizado';

    public static function create(): self
    {
        return new self(self::MESSAGE);
    }
}
