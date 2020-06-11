<?php

declare(strict_types=1);

namespace App\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateTimeExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('toDateFormat', [$this, 'toDateFormat']),
            new TwigFilter('toTimeFormat', [$this, 'toTimeFormat']),
            new TwigFilter('toDateTimeFormat', [$this, 'toDateTimeFormat']),
            new TwigFilter('toDateLocal', [$this, 'toDateLocal']),
            new TwigFilter('toDateTimeLocal', [$this, 'toDateTimeLocal']),
        ];
    }

    public function toDateFormat(\DateTime $date): string
    {
        return $date->format('Y-m-d');
    }

    public function toTimeFormat(\DateTime $date): string
    {
        return $date->format('H:i');
    }

    public function toDateTimeLocal(\DateTime $datetime): string
    {
        return $datetime->format('d/m/Y H:i');
    }
}
