<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Service;

class ModalController extends Controller
{
    public function content(string $type, string $slug)
    {
        if ($type === 'services') {
            $service = Service::query()
                ->where('slug', $slug)
                ->first();
            if (!$service) {
                return response('<p>Serviço não encontrado.</p>', 404);
            }
            // Carregar relações que podem enriquecer o modal, se existirem
            $service->load(['info', 'benefits' => fn($q) => $q->orderBy('order'), 'features' => fn($q) => $q->orderBy('order')]);
            return response()->view('components.content.services.show', compact('service'));
        }

        // Fallback para conteúdos estáticos anteriores
        $view = "components.content.$type.$slug";
        if (view()->exists($view)) {
            return response()->view($view);
        }
        return response('<p>Conteúdo não encontrado.</p>', 404);
    }
}

