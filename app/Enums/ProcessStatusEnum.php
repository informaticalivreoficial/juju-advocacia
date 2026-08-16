<?php

namespace App\Enums;

enum ProcessStatusEnum: string
{
    case Analysis = 'analysis';
    case Active = 'active';
    case AwaitingDecision = 'awaiting_decision';
    case Suspended = 'suspended';
    case Archived = 'archived';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Analysis => 'Em análise',
            self::Active => 'Ativo',
            self::AwaitingDecision => 'Aguardando decisão',
            self::Suspended => 'Suspenso',
            self::Archived => 'Arquivado',
            self::Closed => 'Encerrado',
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
