<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case Admin = 'admin';
    case Partner = 'partner';
    case Lawyer = 'lawyer';
    case Assistant = 'assistant';
    case Secretary = 'secretary';

    /**
     * Rótulo exibido na interface (português).
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Partner => 'Sócio(a)',
            self::Lawyer => 'Advogado(a)',
            self::Assistant => 'Assistente',
            self::Secretary => 'Secretário(a)',
        };
    }

    /**
     * Lista de casos como array associativo para selects.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ])
            ->all();
    }
}
