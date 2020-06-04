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
        ];
    }

    public function toDateFormat(\DateTime $date): string
    {
        return $date->format('Y-m-d');
    }

    public function toTimeFormat(\DateTime $time): string
    {
        return $time->format('H:i');
    }

    public function toDateTimeFormat(\DateTime $datetime): string
    {
        return $datetime->format('Y-m-d\TH:i');
    }
}