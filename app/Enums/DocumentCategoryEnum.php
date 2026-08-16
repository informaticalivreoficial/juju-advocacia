<?php

namespace App\Enums;

enum DocumentCategoryEnum: string
{
    case Contract = 'contract';
    case Petition = 'petition';
    case Decision = 'decision';
    case Certificate = 'certificate';
    case PowerOfAttorney = 'power_of_attorney';
    case Report = 'report';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Contract => 'Contrato',
            self::Petition => 'Petição',
            self::Decision => 'Decisão',
            self::Certificate => 'Certidão',
            self::PowerOfAttorney => 'Procuração',
            self::Report => 'Relatório',
            self::Other => 'Outro',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $category) => [
                'value' => $category->value,
                'label' => $category->label(),
            ])
            ->all();
    }
}
