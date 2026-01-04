<?php

namespace App\Enums;

enum VendorType: string
{
    case Vendor = 'vendor';
    
    public function label(): string
    {
        return 'Vendor';
    }
}
