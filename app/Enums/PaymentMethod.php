<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case CreditCard = 'credit_card';
    case DebitCard = 'debit_card';
    case Check = 'check';
    case PayPal = 'paypal';
    case Stripe = 'stripe';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::BankTransfer => 'Bank Transfer',
            self::CreditCard => 'Credit Card',
            self::DebitCard => 'Debit Card',
            self::Check => 'Check',
            self::PayPal => 'PayPal',
            self::Stripe => 'Stripe',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Cash => 'success',
            self::BankTransfer => 'info',
            self::CreditCard, self::DebitCard => 'primary',
            self::Check => 'warning',
            self::PayPal, self::Stripe => 'secondary',
            self::Other => 'gray',
        };
    }
}
