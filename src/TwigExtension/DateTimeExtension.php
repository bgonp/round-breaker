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
            new TwigFilter('toDateTimeFormat', [$this, 'toDateTimeFormat']),
            new TwigFilter('toDateLocal', [$this, 'toDateLocal']),
            new TwigFilter('toDateTimeLocal', [$this, 'toDateTimeLocal']),
        ];
    }

    public function toDateFormat(\DateTime $date): string
    {
        return $date->format('Y-m-d');
    }

    public function toDateTimeFormat(\DateTime $datetime): string
    {
        return $datetime->format('Y-m-d\TH:i');
    }

    public function toDateLocal(\DateTime $date): string
    {
        return $date->format('d/m/Y');
    }

    public function toDateTimeLocal(\DateTime $datetime): string
    {
        return $datetime->format('d/m/Y H:i');
    }
}