<?php

namespace App\Enums;

enum CloudLeadStatus: string
{
    case NaoIniciado = 'nao_iniciado';
    case EmAndamento = 'em_andamento';
    case Concluido   = 'concluido';

    public function label(): string
    {
        return match($this) {
            self::NaoIniciado => 'Não iniciado',
            self::EmAndamento => 'Em andamento',
            self::Concluido   => 'Concluído',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::NaoIniciado => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            self::EmAndamento => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
            self::Concluido   => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->all();
    }
}
