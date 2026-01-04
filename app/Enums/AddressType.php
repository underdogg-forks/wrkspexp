<?php

namespace App\Enums;

enum AddressType: string
{
    case Billing = 'billing';
    case Shipping = 'shipping';
    case Office = 'office';
    case Home = 'home';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Billing => 'Billing',
            self::Shipping => 'Shipping',
            self::Office => 'Office',
            self::Home => 'Home',
            self::Other => 'Other',
        };
    }
}
