<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidUsernameException extends InvalidPlayerDataException
{
    const MESSAGE = 'Tu nombre de usuario debe tener al menos 5 caracteres de longitud.';

    public static function create(): self
    {
        return new self(parent::TITLE.self::MESSAGE);
    }
}
