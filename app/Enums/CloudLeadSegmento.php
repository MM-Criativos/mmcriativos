<?php

namespace App\Enums;

enum CloudLeadSegmento: string
{
    // ── Estética e Beleza ─────────────────────────────────────────
    case SalaoBeleza      = 'salao_beleza';
    case Barbearia        = 'barbearia';
    case ClinicaEstetica  = 'clinica_estetica';
    case ManicureNail     = 'manicure_nail';
    case TatuagemPiercing = 'tatuagem_piercing';

    // ── Saúde & Bem-estar ─────────────────────────────────────────
    case Odontologista      = 'odontologista';
    case MedicoClinica      = 'medico_clinica';
    case ClinicaSaude       = 'clinica_saude';
    case Hospital           = 'hospital';
    case PsicologoTerapeuta = 'psicologo_terapeuta';
    case Nutricionista      = 'nutricionista';
    case AcademiaPersonal   = 'academia_personal';
    case PilatesYoga        = 'pilates_yoga';

    // ── Serviços Profissionais ────────────────────────────────────
    case Advocacia         = 'advocacia';
    case Contabilidade     = 'contabilidade';
    case ArquiteturaDesign = 'arquitetura_design';
    case Imobiliaria       = 'imobiliaria';

    // ── Varejo & Comércio ─────────────────────────────────────────
    case ModaVestuario = 'moda_vestuario';
    case PetShop       = 'pet_shop';
    case Farmacia      = 'farmacia';

    // ── Educação ──────────────────────────────────────────────────
    case CursosEscola = 'cursos_escola';

    // ── Outros ───────────────────────────────────────────────────
    case Outros = 'outros';

    // ─────────────────────────────────────────────────────────────

    public function label(): string
    {
        return match($this) {
            self::SalaoBeleza      => 'Salão de Beleza',
            self::Barbearia        => 'Barbearia',
            self::ClinicaEstetica  => 'Clínica de Estética',
            self::ManicureNail     => 'Manicure / Nail Designer',
            self::TatuagemPiercing => 'Tatuagem / Piercing',

            self::Odontologista      => 'Odontologista',
            self::MedicoClinica      => 'Médico / Clínica Geral',
            self::ClinicaSaude       => 'Clínica de Saúde',
            self::Hospital           => 'Hospital',
            self::PsicologoTerapeuta => 'Psicólogo / Terapeuta',
            self::Nutricionista      => 'Nutricionista',
            self::AcademiaPersonal   => 'Academia / Personal Trainer',
            self::PilatesYoga        => 'Pilates / Yoga',

            self::Advocacia         => 'Advocacia / Escritório Jurídico',
            self::Contabilidade     => 'Contabilidade',
            self::ArquiteturaDesign => 'Arquitetura / Design de Interiores',
            self::Imobiliaria       => 'Imobiliária / Corretor',

            self::ModaVestuario => 'Moda / Vestuário',
            self::PetShop       => 'Pet Shop',
            self::Farmacia      => 'Farmácia / Drogaria',

            self::CursosEscola => 'Cursos / Escola / Coaching',

            self::Outros => 'Outros',
        };
    }

    public function category(): string
    {
        return match($this) {
            self::SalaoBeleza,
            self::Barbearia,
            self::ClinicaEstetica,
            self::ManicureNail,
            self::TatuagemPiercing      => 'Estética e Beleza',

            self::Odontologista,
            self::MedicoClinica,
            self::ClinicaSaude,
            self::Hospital,
            self::PsicologoTerapeuta,
            self::Nutricionista,
            self::AcademiaPersonal,
            self::PilatesYoga           => 'Saúde & Bem-estar',

            self::Advocacia,
            self::Contabilidade,
            self::ArquiteturaDesign,
            self::Imobiliaria           => 'Serviços Profissionais',

            self::ModaVestuario,
            self::PetShop,
            self::Farmacia              => 'Varejo & Comércio',

            self::CursosEscola          => 'Educação',

            self::Outros                => 'Outros',
        };
    }

    /** Flat: value => label */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
            ->all();
    }

    /** Agrupado por categoria: ['Categoria' => ['value' => 'label']] */
    public static function groupedOptions(): array
    {
        $grouped = [];
        foreach (self::cases() as $case) {
            $grouped[$case->category()][$case->value] = $case->label();
        }
        return $grouped;
    }
}
