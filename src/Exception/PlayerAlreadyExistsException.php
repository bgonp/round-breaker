<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class PlayerAlreadyExistsException extends BadRequestException
{
    const MESSAGE = 'Los siguientes campos ya están en uso por otro jugador: %s. Por favor, usa otros datos.';

    public static function create(array $fields): self
    {
        return new self(sprintf(self::MESSAGE, implode(', ', $fields)));
    }
}