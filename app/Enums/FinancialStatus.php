<?php

namespace App\Enums;

enum FinancialStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Paid => 'Pago',
            self::Received => 'Recebido',
            self::Cancelled => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Paid => 'green',
            self::Received => 'green',
            self::Cancelled => 'slate',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->all();
    }
}
