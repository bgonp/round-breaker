<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidPlayerDataException extends BadRequestHttpException
{
    const MESSAGE = 'Usuario existente o campos incorrectos: %s';

    public static function create(array $invalidFields): self
    {
        return new self(sprintf(self::MESSAGE, implode(', ', $invalidFields)));
    }
}
