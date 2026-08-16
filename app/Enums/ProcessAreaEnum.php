<?php

namespace App\Enums;

enum ProcessAreaEnum: string
{
    case Civil = 'civil';
    case Labor = 'labor';
    case Family = 'family';
    case Criminal = 'criminal';
    case Business = 'business';
    case Tax = 'tax';
    case Consumer = 'consumer';
    case SocialSecurity = 'social_security';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Civil => 'Cível',
            self::Labor => 'Trabalhista',
            self::Family => 'Família',
            self::Criminal => 'Criminal',
            self::Business => 'Empresarial',
            self::Tax => 'Tributário',
            self::Consumer => 'Consumidor',
            self::SocialSecurity => 'Previdenciário',
            self::Other => 'Outro',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $area) => [
                'value' => $area->value,
                'label' => $area->label(),
            ])
            ->all();
    }
}
