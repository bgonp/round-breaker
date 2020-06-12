<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidTwitchNameException extends InvalidPlayerDataException
{
    const MESSAGE = 'Tu nombre en twitch debe tener al menos 6 caracteres y solo se admiten letras, números y "_".';

    public static function create(): self
    {
        return new self(parent::TITLE.self::MESSAGE);
    }
}
