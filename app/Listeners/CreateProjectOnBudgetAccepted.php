<?php
// App/Listeners/CreateProjectOnBudgetAccepted.php
namespace App\Listeners;

use App\Events\BudgetAccepted;
use App\Mail\ProjectPerceptionBriefingMail;
use App\Models\Project;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class CreateProjectOnBudgetAccepted
{
    public function handle(BudgetAccepted $event): void
    {
        $budget = $event->budget;
        if (!$budget->client_id || !$budget->service_id) {
            return;
        }

        $serviceName = $budget->service->name ?? 'Serviço';
        $clientName  = $budget->client->name ?? $budget->client_name ?? '';
        $name = trim($serviceName . ' ' . $clientName);

        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $i = 2;
        while (Project::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $project = Project::create([
            'name'       => $name,
            'slug'       => $slug,
            'client_id'  => $budget->client_id,
            'service_id' => $budget->service_id,
        ]);

        // Envia e-mail com link assinado para a régua de percepção
        try {
            $to = $budget->client->email ?? $budget->email ?? null;
            if ($to) {
                $link = URL::temporarySignedRoute(
                    'public.briefing.perception', now()->addDays(14), ['project' => $project->id]
                );
                Mail::to($to)->send(new ProjectPerceptionBriefingMail($clientName ?: 'cliente', $link));
            }
        } catch (\Throwable $e) {
            // Silencia falhas de envio para não interromper a criação do projeto
        }
    }
}
