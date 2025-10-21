<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ClientInfoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'cep' => ['nullable', 'string', 'max:20'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'state_code' => ['nullable', 'string', 'max:5'],
            'country' => ['nullable', 'string', 'max:255'],
            'email_commercial' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'phone_alt' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
        ]);

        $client->info()->updateOrCreate([], $data);

        return back()->with('status', 'Informações de endereço/contato salvas.');
    }

    public function getAddressByCep($cep)
    {
        // Remove caracteres não numéricos
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            return response()->json(['error' => 'CEP inválido'], 400);
        }

        // Consulta na API do ViaCEP
        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        if ($response->failed() || isset($response['erro'])) {
            return response()->json(['error' => 'CEP não encontrado'], 404);
        }

        return response()->json([
            'cep'          => $response['cep'],
            'street'       => $response['logradouro'],
            'complement'   => $response['complemento'],
            'neighborhood' => $response['bairro'],
            'city'         => $response['localidade'],
            'state'        => $response['uf'],
        ]);
    }
}
