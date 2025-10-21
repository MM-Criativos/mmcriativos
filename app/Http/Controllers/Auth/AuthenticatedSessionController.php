<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Exibe a tela de login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Processa o login do usuário.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Tenta autenticar o usuário
        $request->authenticate();

        // Regenera a sessão para evitar fixation
        $request->session()->regenerate();

        // Usuário não aprovado: mostra tela de pendência e redireciona ao site
        if (!Auth::user()->is_approved) {
            Auth::logout();
            return redirect()->route('pending.approval');
        }

        // Redireciona para o dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Faz logout da sessão autenticada.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

