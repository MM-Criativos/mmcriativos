<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── vee_project_events ────────────────────────────────────────────────
        // Append-only event log for any entity (project, task, appointment, etc.)
        // Core of the traceability timeline.
        Schema::create('vee_project_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->string('entity_type', 80)->index();        // project, task, appointment, …
            $table->unsignedBigInteger('entity_id')->index();
            $table->string('actor', 120)->index();             // "Vee", "system", user name
            $table->string('action', 120)->index();            // status_changed, created, blocked, …
            $table->json('payload')->nullable();               // event-specific data
            $table->json('context')->nullable();               // surrounding context
            $table->timestamp('created_at')->useCurrent()->index();
            // No updated_at — append-only
        });

        // ── vee_status_history ────────────────────────────────────────────────
        // Before/after record every time an entity's status changes.
        Schema::create('vee_status_history', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 80)->index();
            $table->unsignedBigInteger('entity_id')->index();
            $table->string('old_status', 80)->nullable();
            $table->string('new_status', 80)->index();
            $table->string('changed_by', 120);
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            // No updated_at — append-only
        });

        // ── vee_operational_decisions ─────────────────────────────────────────
        // Institutional memory: decisions, their context, rationale, and outcome.
        Schema::create('vee_operational_decisions', function (Blueprint $table) {
            $table->id();
            $table->uuid('decision_id')->unique();
            $table->string('title', 255);
            $table->text('context');
            $table->text('rationale');
            $table->text('outcome');
            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();
            $table->string('actor', 120)->index();
            $table->timestamps();
        });

        // ── vee_blocks ────────────────────────────────────────────────────────
        // Block/unblock log. Active block = unblocked_at IS NULL.
        Schema::create('vee_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 80)->index();
            $table->unsignedBigInteger('entity_id')->index();
            $table->text('reason');
            $table->string('blocked_by', 120);
            $table->timestamp('blocked_at')->useCurrent();
            $table->timestamp('unblocked_at')->nullable();
            $table->text('unblock_reason')->nullable();
            $table->string('unblocked_by', 120)->nullable();
            // Composite index: look up active blocks for an entity quickly
            $table->index(['entity_type', 'entity_id', 'unblocked_at'], 'idx_blocks_entity_active');
        });

        // ── MySQL views ───────────────────────────────────────────────────────

        DB::statement("
            CREATE OR REPLACE VIEW v_project_timeline AS
            SELECT
                pe.id,
                pe.event_id,
                pe.entity_type,
                pe.entity_id,
                pe.actor,
                pe.action,
                p.name       AS project_name,
                p.slug       AS project_slug,
                cl.name      AS client_name,
                pe.payload,
                pe.context,
                pe.created_at
            FROM vee_project_events pe
            LEFT JOIN projects p  ON pe.entity_type = 'project' AND p.id = pe.entity_id
            LEFT JOIN clients cl  ON cl.id = p.client_id
            ORDER BY pe.created_at DESC
        ");

        DB::statement("
            CREATE OR REPLACE VIEW v_active_projects AS
            SELECT
                p.id,
                p.name,
                p.slug,
                p.summary,
                p.status,
                p.created_at,
                p.finished_at,
                cl.name  AS client_name,
                sv.name  AS service_name,
                COUNT(pt.id)                                                   AS tasks_total,
                SUM(CASE WHEN pt.status = 'pending'      THEN 1 ELSE 0 END)   AS tasks_pending,
                SUM(CASE WHEN pt.status = 'in_progress'  THEN 1 ELSE 0 END)   AS tasks_in_progress,
                SUM(CASE WHEN pt.status = 'done'         THEN 1 ELSE 0 END)   AS tasks_done
            FROM projects p
            LEFT JOIN clients cl          ON cl.id = p.client_id
            LEFT JOIN services sv         ON sv.id = p.service_id
            LEFT JOIN project_tasks pt    ON pt.project_id = p.id
            WHERE p.status IN ('active', 'paused')
            GROUP BY p.id, p.name, p.slug, p.summary, p.status, p.created_at, p.finished_at,
                     cl.name, sv.name
        ");

        DB::statement("
            CREATE OR REPLACE VIEW v_pending_work AS
            SELECT
                pt.id,
                pt.title,
                pt.description,
                pt.status,
                pt.created_at,
                p.name   AS project_name,
                p.slug   AS project_slug,
                cl.name  AS client_name,
                u.name   AS assignee_name
            FROM project_tasks pt
            LEFT JOIN projects p  ON p.id  = pt.project_id
            LEFT JOIN clients cl  ON cl.id = p.client_id
            LEFT JOIN users u     ON u.id  = pt.assigned_to
            WHERE pt.status IN ('pending', 'in_progress')
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_pending_work');
        DB::statement('DROP VIEW IF EXISTS v_active_projects');
        DB::statement('DROP VIEW IF EXISTS v_project_timeline');

        Schema::dropIfExists('vee_blocks');
        Schema::dropIfExists('vee_operational_decisions');
        Schema::dropIfExists('vee_status_history');
        Schema::dropIfExists('vee_project_events');
    }
};
