<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Employee = 'employee';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Manager => 'Manager',
            self::Employee => 'Employee',
            self::Client => 'Client',
        };
    }
}
