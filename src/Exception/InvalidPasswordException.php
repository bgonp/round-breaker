<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidPasswordException extends InvalidPlayerDataException
{
    const MESSAGE = 'Tu contraseña debe tener al menos 6 caracteres de longitud.';

    public static function create(): self
    {
        return new self(parent::TITLE.self::MESSAGE);
    }
}
