<?php

namespace App\Enums;

enum DeadlinePriorityEnum: string
{
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::High => 'Alta',
            self::Urgent => 'Urgente',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $priority) => [
                'value' => $priority->value,
                'label' => $priority->label(),
            ])
            ->all();
    }
}
