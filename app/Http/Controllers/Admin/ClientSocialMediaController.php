<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\SocialMedia;
use Illuminate\Http\Request;

class ClientSocialMediaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function upsert(Request $request, Client $client, SocialMedia $socialMedia)
    {
        $data = $request->validate([
            'user' => ['nullable','string','max:255'],
        ]);
        // Se vazio, remove o registro existente em vez de salvar nulo
        if (empty($data['user'])) {
            $client->clientSocialMedia()->where('social_media_id', $socialMedia->id)->delete();
            return back()->with('status', 'Rede social removida.');
        }

        $client->clientSocialMedia()->updateOrCreate(
            ['social_media_id' => $socialMedia->id],
            ['user' => $data['user']]
        );
        return back()->with('status','Rede social atualizada.');
    }

    public function destroy(Client $client, SocialMedia $socialMedia)
    {
        $client->clientSocialMedia()->where('social_media_id', $socialMedia->id)->delete();
        return back()->with('status','Rede social removida.');
    }
}
