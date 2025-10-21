<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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

