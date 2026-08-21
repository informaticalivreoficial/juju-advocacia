<?php

namespace App\Enums;

enum FinancialPaymentMethod: string
{
    case Pix = 'pix';
    case Boleto = 'boleto';
    case Card = 'cartao';
    case Transfer = 'transferencia';
    case Cash = 'dinheiro';

    public function label(): string
    {
        return match ($this) {
            self::Pix => 'PIX',
            self::Boleto => 'Boleto',
            self::Card => 'Cartão',
            self::Transfer => 'Transferência',
            self::Cash => 'Dinheiro',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $method) => [
                'value' => $method->value,
                'label' => $method->label(),
            ])
            ->all();
    }
}
