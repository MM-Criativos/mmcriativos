<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Services\Upload\ImageUploadService;
use App\Support\StorageHelper;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Client $client)
    {
        $contacts = $client->contacts()->orderByDesc('is_primary')->orderBy('id')->get();
        return view('admin.clients.contact.index', compact('client', 'contacts'));
    }

    public function create(Client $client)
    {
        return view('admin.clients.contact.create', compact('client'));
    }

    public function store(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image'],
        ]);
        $data['is_primary'] = (bool)($data['is_primary'] ?? false);
        if ($request->hasFile('photo')) {
            // sem anterior para novo contato
            /** @var ImageUploadService $uploader */
            $uploader = app(ImageUploadService::class);
            $basename = 'client-' . ($client->slug ?? 'client') . '-contact-' . \Illuminate\Support\Str::slug($data['name'] ?? 'contact', '-') . '-photo';
            $path = $uploader->store($request->file('photo'), 'clients/contacts', ['basename' => $basename]);
            $data['photo'] = 'storage/' . $path;
        }
        $contact = $client->contacts()->create($data);
        if ($contact->is_primary) {
            $client->contacts()->where('id', '<>', $contact->id)->update(['is_primary' => false]);
        }
        return redirect()->route('admin.clients.contacts.index', $client)->with('status', 'Contato adicionado.');
    }

    public function edit(Contact $contact)
    {
        $client = $contact->client;
        return view('admin.clients.contact.edit', compact('client', 'contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image'],
        ]);
        $data['is_primary'] = (bool)($data['is_primary'] ?? false);
        if ($request->hasFile('photo')) {
            // apaga anterior
            StorageHelper::deletePublic($contact->photo);
            /** @var ImageUploadService $uploader */
            $uploader = app(ImageUploadService::class);
            $basename = 'client-' . ($contact->client->slug ?? 'client') . '-contact-' . \Illuminate\Support\Str::slug($data['name'] ?? $contact->name ?? 'contact', '-') . '-photo';
            $path = $uploader->store($request->file('photo'), 'clients/contacts', ['basename' => $basename]);
            $data['photo'] = 'storage/' . $path;
        }
        $contact->update($data);
        if ($contact->is_primary) {
            $contact->client->contacts()->where('id', '<>', $contact->id)->update(['is_primary' => false]);
        }
        return back()->with('status', 'Contato atualizado.');
    }

    public function destroy(Contact $contact)
    {
        $client = $contact->client;
        $contact->delete();
        return redirect()->route('admin.clients.contacts.index', $client)->with('status', 'Contato removido.');
    }

    // Endpoint para select dependente no Testimonials create
    public function select(Client $client)
    {
        return response()->json(
            $client->contacts()->orderByDesc('is_primary')->orderBy('id')->get(['id', 'name'])
        );
    }
}
