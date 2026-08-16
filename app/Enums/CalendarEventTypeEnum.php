<?php

namespace App\Enums;

enum CalendarEventTypeEnum: string
{
    case Hearing = 'hearing';
    case Appointment = 'appointment';
    case Meeting = 'meeting';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Hearing => 'Audiência',
            self::Appointment => 'Compromisso',
            self::Meeting => 'Reunião',
            self::Other => 'Outro',
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
