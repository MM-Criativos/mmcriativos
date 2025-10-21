<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientTestimonial;
use App\Models\Contact;
use Illuminate\Http\Request;

class ClientTestimonialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $testimonials = ClientTestimonial::with(['client', 'contact'])->orderBy('id', 'desc')->get();
        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        return view('admin.testimonials.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'contact_id' => ['required', 'exists:contacts,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'testimonial' => ['required', 'string'],
        ]);
        $t = ClientTestimonial::create($data);
        return redirect()->route('admin.testimonials.edit', $t)->with('status', 'Depoimento criado.');
    }

    public function edit(ClientTestimonial $testimonial)
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $contacts = Contact::where('client_id', $testimonial->client_id)->orderByDesc('is_primary')->orderBy('id')->get(['id', 'name']);
        return view('admin.testimonials.edit', compact('testimonial', 'clients', 'contacts'));
    }

    public function update(Request $request, ClientTestimonial $testimonial)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'contact_id' => ['required', 'exists:contacts,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'testimonial' => ['required', 'string'],
        ]);
        $testimonial->update($data);
        return back()->with('status', 'Depoimento atualizado.');
    }

    public function destroy(ClientTestimonial $testimonial)
    {
        $testimonial->delete();
        return redirect()->route('admin.testimonials.index')->with('status', 'Depoimento removido.');
    }
}
