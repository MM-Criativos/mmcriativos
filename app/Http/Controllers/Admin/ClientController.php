<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\Upload\ImageUploadService;
use App\Support\StorageHelper;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $clients = Client::orderBy('id')->get();
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        $client = new Client();
        return view('admin.clients.create', compact('client'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:clients,slug'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB
            'website' => ['nullable', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        // Gera slug se não vier
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Upload da logo (otimizada)
        if ($request->hasFile('logo')) {
            /** @var ImageUploadService $uploader */
            $uploader = app(ImageUploadService::class);
            $slug = $data['slug'];
            $basename = "client-{$slug}-logo";
            $path = $uploader->store($request->file('logo'), 'clients/logos', ['basename' => $basename]);
            $data['logo'] = $path; // salva o caminho relativo
        }

        $client = Client::create($data);

        return redirect()
            ->route('admin.clients.edit', $client)
            ->with('status', 'Cliente criado com sucesso.');
    }
    public function edit(Client $client)
    {
        $client->load([
            'info',
            'clientSocialMedia.socialMedia',
            'contacts' => function ($q) {
                $q->orderByDesc('is_primary')->orderBy('id');
            },
        ]);

        $socials = SocialMedia::orderBy('name')->get();

        return view('admin.clients.edit', compact('client', 'socials'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:clients,slug,' . $client->id],
            'logo' => ['nullable', 'image'],
            'website' => ['nullable', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            if ($client->logo && Storage::disk('public')->exists($client->logo)) {
                Storage::disk('public')->delete($client->logo);
            }

            /** @var ImageUploadService $uploader */
            $uploader = app(ImageUploadService::class);
            $basename = "client-{$client->slug}-logo";
            $path = $uploader->store($request->file('logo'), 'clients/logos', ['basename' => $basename]);
            $data['logo'] = $path;
        }

        $client->update($data);

        return back()->with('status', 'Informações do cliente atualizadas.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('admin.clients.index')->with('status', 'Cliente removido.');
    }
}
