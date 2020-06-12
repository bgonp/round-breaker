<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

abstract class InvalidPlayerDataException extends BadRequestException
{
    const TITLE = 'Datos de usuario incorrectos. ';
}
