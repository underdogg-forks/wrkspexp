<?php

namespace App\Enums;

enum RelationType: string
{
    case Client = 'client';
    case Contractor = 'contractor';
    case Partner = 'partner';
    case Prospect = 'prospect';
    case Supplier = 'supplier';
    case Vendor = 'vendor';

    public function label(): string
    {
        return match ($this) {
            self::Client => 'Client',
            self::Contractor => 'Contractor',
            self::Partner => 'Partner',
            self::Prospect => 'Prospect',
            self::Supplier => 'Supplier',
            self::Vendor => 'Vendor',
        };
    }
}
