<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CannotDeleteGameException extends BadRequestHttpException
{
    const MESSAGE = 'No se puede borrar un juego si se han organizado torneos de éste';

    public static function create(): self
    {
        return new self(self::MESSAGE);
    }
}
