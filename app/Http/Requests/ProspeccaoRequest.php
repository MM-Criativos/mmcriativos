<?php

namespace App\Http\Requests;

use App\Enums\CloudLeadEtapa;
use App\Enums\CloudLeadSegmento;
use App\Enums\CloudLeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ProspeccaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa'            => ['required', 'string', 'max:255'],
            'segmento'           => ['nullable', new Enum(CloudLeadSegmento::class)],
            'status'             => ['required', new Enum(CloudLeadStatus::class)],
            'etapa'              => ['required', new Enum(CloudLeadEtapa::class)],
            'responsavel_id'     => ['nullable', 'exists:users,id'],
            'contato'            => ['nullable', 'string', 'max:255'],
            'seguidores'         => ['nullable', 'string', 'max:50'],
            'tomador_de_decisao' => ['nullable', 'string', 'max:255'],
            'instagram_url'      => ['nullable', 'url', 'max:500'],
            'descricao'          => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'empresa.required'  => 'O nome da empresa é obrigatório.',
            'status.required'   => 'O status é obrigatório.',
            'etapa.required'    => 'A etapa é obrigatória.',
            'instagram_url.url' => 'O link do Instagram deve ser uma URL válida.',
        ];
    }
}
