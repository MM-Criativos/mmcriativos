<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $setting = Setting::first();
        return view('admin.settings.index', compact('setting'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'address_zipcode' => ['nullable','string','max:255'],
            'address_street' => ['nullable','string','max:255'],
            'address_number' => ['nullable','string','max:255'],
            'address_complement' => ['nullable','string','max:255'],
            'address_neighborhood' => ['nullable','string','max:255'],
            'address_city' => ['nullable','string','max:255'],
            'address_state' => ['nullable','string','max:255'],
            'address_country' => ['nullable','string','max:255'],

            'phone' => ['nullable','string','max:255'],
            'email_support' => ['nullable','email','max:255'],
            'email_contact' => ['nullable','email','max:255'],
            'email_commercial' => ['nullable','email','max:255'],

            'facebook' => ['nullable','string','max:255'],
            'instagram' => ['nullable','string','max:255'],
            'tiktok' => ['nullable','string','max:255'],
            'x' => ['nullable','string','max:255'],
            'linkedin' => ['nullable','string','max:255'],
            'youtube' => ['nullable','string','max:255'],
            'behance' => ['nullable','string','max:255'],
            'dribbble' => ['nullable','string','max:255'],
            'github' => ['nullable','string','max:255'],
            'whatsapp' => ['nullable','string','max:255'],
        ]);

        $setting = Setting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            $setting = Setting::create($data);
        }

        if ($request->ajax()) {
            return response()->json(['status' => 'ok']);
        }
        return back()->with('status', 'Configurações salvas.');
    }
}

