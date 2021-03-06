<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class RegistrationAlreadyExistsException extends BadRequestException
{
    const MESSAGE = 'El usuario ya esta inscrito a esta competición.';

    public static function create(): self
    {
        return new self(self::MESSAGE);
    }
}
