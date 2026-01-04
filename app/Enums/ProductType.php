<?php

namespace App\Enums;

enum ProductType: string
{
    case Service = 'service';
    case Physical = 'physical';

    public function label(): string
    {
        return match ($this) {
            self::Service => 'Service',
            self::Physical => 'Physical Product',
        };
    }
}
