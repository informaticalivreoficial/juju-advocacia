<?php

namespace App\Enums;

enum FinancialType: string
{
    case Income = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Receita',
            self::Expense => 'Despesa',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Income => 'green',
            self::Expense => 'red',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->all();
    }
}
