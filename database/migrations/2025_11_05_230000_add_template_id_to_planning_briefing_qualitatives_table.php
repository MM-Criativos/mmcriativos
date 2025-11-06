<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planning_briefing_qualitatives', function (Blueprint $table) {
            $table->foreignId('template_id')
                ->nullable()
                ->after('client_id')
                ->constrained('qualitative_templates')
                ->nullOnDelete();
        });

        $records = DB::table('planning_briefing_qualitatives')->get();

        foreach ($records as $record) {
            $selected = $record->selected_templates;
            if (is_string($selected)) {
                $selected = json_decode($selected, true);
            }

            if (empty($selected) || !is_array($selected)) {
                continue;
            }

            $templateIds = collect($selected)
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values();

            if ($templateIds->isEmpty()) {
                continue;
            }

            $firstTemplateId = $templateIds->shift();

            DB::table('planning_briefing_qualitatives')
                ->where('id', $record->id)
                ->update([
                    'template_id' => $firstTemplateId,
                    'selected_templates' => null,
                ]);

            foreach ($templateIds as $templateId) {
                DB::table('planning_briefing_qualitatives')->insert([
                    'project_id' => $record->project_id,
                    'client_id' => $record->client_id,
                    'template_id' => $templateId,
                    'title' => $record->title,
                    'status' => $record->status,
                    'selected_templates' => null,
                    'meta' => $record->meta,
                    'started_at' => $record->started_at,
                    'completed_at' => $record->completed_at,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('planning_briefing_qualitatives', function (Blueprint $table) {
            $table->dropConstrainedForeignId('template_id');
        });
    }
};
