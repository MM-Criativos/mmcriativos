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
        $budget = $event->budget->loadMissing(['project', 'service', 'client']);

        if (! $budget->client_id || ! $budget->service_id) {
            return;
        }

        if ($budget->project) {
            $project = $budget->project;
        } else {
            $serviceName = $budget->service->name ?? 'Serviço';
            $clientName  = $budget->client->name ?? $budget->client_name ?? '';
            $name = trim($serviceName . ' ' . $clientName);

            $baseSlug = Str::slug($name) ?: 'projeto';
            $slug = $baseSlug;
            $i = 2;
            while (Project::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }

            $project = Project::firstOrCreate(
                ['budget_id' => $budget->id],
                [
                    'name'       => $name,
                    'slug'       => $slug,
                    'client_id'  => $budget->client_id,
                    'service_id' => $budget->service_id,
                ]
            );
        }

        try {
            $to = $budget->client->email ?? $budget->client_email ?? null;
            if ($to) {
                $link = URL::temporarySignedRoute(
                    'public.briefing.perception',
                    now()->addDays(14),
                    ['project' => $project->id]
                );

                Mail::to($to)->send(
                    new ProjectPerceptionBriefingMail(
                        $budget->client->name ?? $budget->client_name ?? 'cliente',
                        $link
                    )
                );
            }
        } catch (\Throwable $e) {
            // Ignore e-mail failures
        }
    }
}
