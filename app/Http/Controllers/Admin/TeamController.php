<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use App\Services\Upload\ImageUploadService;
use App\Support\StorageHelper;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()?->role !== 'admin') {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $users = User::query()->orderBy('name')->get();
        return view('admin.team.index', compact('users'));
    }

    public function edit(User $user)
    {
        $socialMedias = SocialMedia::orderBy('name')->get();
        $classes = \App\Models\Classe::orderBy('hierarquia')->orderBy('classe')->get();
        $user->load(['socialMedias', 'classes']);
        return view('admin.team.edit', compact('user', 'socialMedias', 'classes'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'cargo' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image'],
            'socials' => ['nullable', 'array'],
            'socials.*' => ['nullable', 'string', 'max:2048'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['integer', 'exists:classes,id'],
        ]);

        if ($request->hasFile('photo')) {
            StorageHelper::deletePublic($user->photo);
            /** @var ImageUploadService $img */
            $img = app(ImageUploadService::class);
            $basename = 'user-' . \Illuminate\Support\Str::slug($user->name ?: 'user', '-') . '-photo';
            $path = $img->store($request->file('photo'), 'users', ['basename' => $basename]);
            $data['photo'] = 'storage/' . $path;
        } else {
            unset($data['photo']);
        }

        // Atualiza dados básicos
        $user->update($data);

        // Sincroniza redes sociais (id => url)
        $sync = [];
        foreach ((array) ($data['socials'] ?? []) as $socialId => $url) {
            $url = trim((string) $url);
            if ($url !== '') {
                $sync[(int) $socialId] = ['url' => $url];
            }
        }
        $user->socialMedias()->sync($sync);

        // Sincroniza classes
        $classIds = collect($data['classes'] ?? [])->filter()->map(fn ($id) => (int) $id)->all();
        $user->classes()->sync($classIds);

        return redirect()->route('admin.team.edit', $user)->with('status', 'Usuário atualizado.');
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', 'in:admin,user']]);
        // Evita perder o último admin
        if ($user->role === 'admin' && $data['role'] === 'user') {
            $otherAdmins = User::where('role', 'admin')->where('id', '!=', $user->id)->count();
            if ($otherAdmins === 0) {
                return back()->with('status', 'É necessário manter pelo menos um administrador.');
            }
        }
        $user->role = $data['role'];
        $user->save();
        return back()->with('status', 'Cargo atualizado.');
    }

    public function approve(User $user)
    {
        $user->is_approved = true;
        $user->save();
        return back()->with('status', 'Usuário aprovado.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('status', 'Você não pode excluir sua própria conta.');
        }
        $user->delete();
        return back()->with('status', 'Usuário excluído.');
    }
}
