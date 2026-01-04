<?php

namespace App\Enums;

enum RelationType: string
{
    case Client = 'client';
    case Supplier = 'supplier';
    case Partner = 'partner';
    case Contractor = 'contractor';

    public function label(): string
    {
        return match ($this) {
            self::Client => 'Client',
            self::Supplier => 'Supplier',
            self::Partner => 'Partner',
            self::Contractor => 'Contractor',
        };
    }
}
