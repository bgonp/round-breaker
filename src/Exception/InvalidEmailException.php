<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidEmailException extends InvalidPlayerDataException
{
    const MESSAGE = 'Por favor, introduce un correo electrónico válido.';

    public static function create(): self
    {
        return new self(parent::TITLE.self::MESSAGE);
    }
}