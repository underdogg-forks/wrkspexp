<?php

namespace App\Enums;

enum ProspectType: string
{
    case Prospect = 'prospect';
    
    public function label(): string
    {
        return 'Prospect';
    }
}
