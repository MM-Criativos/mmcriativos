<?php

namespace App\Enums;

enum CloudLeadEtapa: string
{
    case NovoLead        = 'novo_lead';
    case Iniciado        = 'iniciado';
    case Respondeu       = 'respondeu';
    case Engajado        = 'engajado';
    case DorIdentificado = 'dor_identificado';
    case Qualificado     = 'qualificado';
    case ReuniaoAgendada = 'reuniao_agendada';
    case FollowUp        = 'follow_up';
    case Perdido         = 'perdido';

    public function label(): string
    {
        return match($this) {
            self::NovoLead        => 'Novo Lead',
            self::Iniciado        => 'Iniciado',
            self::Respondeu       => 'Respondeu',
            self::Engajado        => 'Engajado',
            self::DorIdentificado => 'Dor Identificado',
            self::Qualificado     => 'Qualificado',
            self::ReuniaoAgendada => 'Reunião Agendada',
            self::FollowUp        => 'Follow Up',
            self::Perdido         => 'Perdido',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::NovoLead        => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            self::Iniciado        => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
            self::Respondeu       => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
            self::Engajado        => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
            self::DorIdentificado => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
            self::Qualificado     => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
            self::ReuniaoAgendada => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
            self::FollowUp        => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
            self::Perdido         => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
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
