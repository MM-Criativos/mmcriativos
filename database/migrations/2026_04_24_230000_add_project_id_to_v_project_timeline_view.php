<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_project_timeline AS
            SELECT
                timeline.id,
                timeline.event_id,
                timeline.entity_type,
                timeline.entity_id,
                timeline.project_id,
                timeline.actor,
                timeline.action,
                p.name AS project_name,
                p.slug AS project_slug,
                cl.name AS client_name,
                timeline.payload,
                timeline.context,
                timeline.created_at
            FROM (
                SELECT
                    pe.id,
                    pe.event_id,
                    pe.entity_type,
                    pe.entity_id,
                    COALESCE(
                        CASE
                            WHEN pe.entity_type = 'project' THEN pe.entity_id
                            ELSE NULL
                        END,
                        NULLIF(CAST(JSON_UNQUOTE(JSON_EXTRACT(pe.context, '$.project_id')) AS UNSIGNED), 0),
                        NULLIF(CAST(JSON_UNQUOTE(JSON_EXTRACT(pe.payload, '$.project_id')) AS UNSIGNED), 0),
                        NULLIF(CAST(JSON_UNQUOTE(JSON_EXTRACT(pe.payload, '$.after.project_id')) AS UNSIGNED), 0),
                        NULLIF(CAST(JSON_UNQUOTE(JSON_EXTRACT(pe.payload, '$.before.project_id')) AS UNSIGNED), 0),
                        CASE
                            WHEN pe.entity_type IN ('task', 'project_task') THEN pt.project_id
                            ELSE NULL
                        END
                    ) AS project_id,
                    pe.actor,
                    pe.action,
                    pe.payload,
                    pe.context,
                    pe.created_at
                FROM vee_project_events pe
                LEFT JOIN project_tasks pt
                    ON pe.entity_type IN ('task', 'project_task')
                   AND pt.id = pe.entity_id
            ) AS timeline
            LEFT JOIN projects p
                ON p.id = timeline.project_id
            LEFT JOIN clients cl
                ON cl.id = p.client_id
            ORDER BY timeline.created_at DESC
        ");
    }

    public function down(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_project_timeline AS
            SELECT
                pe.id,
                pe.event_id,
                pe.entity_type,
                pe.entity_id,
                pe.actor,
                pe.action,
                p.name AS project_name,
                p.slug AS project_slug,
                cl.name AS client_name,
                pe.payload,
                pe.context,
                pe.created_at
            FROM vee_project_events pe
            LEFT JOIN projects p
                ON pe.entity_type = 'project'
               AND p.id = pe.entity_id
            LEFT JOIN clients cl
                ON cl.id = p.client_id
            ORDER BY pe.created_at DESC
        ");
    }
};
