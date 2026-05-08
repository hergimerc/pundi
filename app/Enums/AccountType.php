<?php

namespace App\Enums;

enum AccountType: string
{
    case Cash = 'cash';
    case BankAccount = 'bank_account';
    case CreditCard = 'credit_card';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::BankAccount => 'Bank Account',
            self::CreditCard => 'Credit Card',
        };
    }
}
