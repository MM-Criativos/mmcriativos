<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vee_messages')) {
            return;
        }

        Schema::table('vee_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('vee_messages', 'input_tokens')) {
                $table->unsignedInteger('input_tokens')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'output_tokens')) {
                $table->unsignedInteger('output_tokens')->nullable();
            }

            // MMCC-like usage structure
            if (! Schema::hasColumn('vee_messages', 'provider')) {
                $table->string('provider', 40)->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'model')) {
                $table->string('model')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'feature')) {
                $table->string('feature', 80)->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'is_billable')) {
                $table->boolean('is_billable')->default(true);
            }

            if (! Schema::hasColumn('vee_messages', 'reference_type')) {
                $table->string('reference_type')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'response_id')) {
                $table->string('response_id')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'external_message_id')) {
                $table->string('external_message_id')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'external_conversation_id')) {
                $table->string('external_conversation_id')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'prompt_tokens')) {
                $table->unsignedInteger('prompt_tokens')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'cached_tokens')) {
                $table->unsignedInteger('cached_tokens')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'completion_tokens')) {
                $table->unsignedInteger('completion_tokens')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'reasoning_tokens')) {
                $table->unsignedInteger('reasoning_tokens')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'total_tokens')) {
                $table->unsignedInteger('total_tokens')->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'estimated_cost')) {
                $table->decimal('estimated_cost', 12, 6)->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'channel')) {
                $table->string('channel', 40)->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'source')) {
                $table->string('source', 40)->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'entrypoint')) {
                $table->string('entrypoint', 40)->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'component')) {
                $table->string('component', 80)->nullable();
            }

            if (! Schema::hasColumn('vee_messages', 'metadata')) {
                $table->json('metadata')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vee_messages')) {
            return;
        }

        Schema::table('vee_messages', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('vee_messages', 'metadata')) {
                $dropColumns[] = 'metadata';
            }

            if (Schema::hasColumn('vee_messages', 'component')) {
                $dropColumns[] = 'component';
            }

            if (Schema::hasColumn('vee_messages', 'entrypoint')) {
                $dropColumns[] = 'entrypoint';
            }

            if (Schema::hasColumn('vee_messages', 'source')) {
                $dropColumns[] = 'source';
            }

            if (Schema::hasColumn('vee_messages', 'channel')) {
                $dropColumns[] = 'channel';
            }

            if (Schema::hasColumn('vee_messages', 'estimated_cost')) {
                $dropColumns[] = 'estimated_cost';
            }

            if (Schema::hasColumn('vee_messages', 'total_tokens')) {
                $dropColumns[] = 'total_tokens';
            }

            if (Schema::hasColumn('vee_messages', 'reasoning_tokens')) {
                $dropColumns[] = 'reasoning_tokens';
            }

            if (Schema::hasColumn('vee_messages', 'completion_tokens')) {
                $dropColumns[] = 'completion_tokens';
            }

            if (Schema::hasColumn('vee_messages', 'cached_tokens')) {
                $dropColumns[] = 'cached_tokens';
            }

            if (Schema::hasColumn('vee_messages', 'prompt_tokens')) {
                $dropColumns[] = 'prompt_tokens';
            }

            if (Schema::hasColumn('vee_messages', 'external_conversation_id')) {
                $dropColumns[] = 'external_conversation_id';
            }

            if (Schema::hasColumn('vee_messages', 'external_message_id')) {
                $dropColumns[] = 'external_message_id';
            }

            if (Schema::hasColumn('vee_messages', 'response_id')) {
                $dropColumns[] = 'response_id';
            }

            if (Schema::hasColumn('vee_messages', 'reference_id')) {
                $dropColumns[] = 'reference_id';
            }

            if (Schema::hasColumn('vee_messages', 'reference_type')) {
                $dropColumns[] = 'reference_type';
            }

            if (Schema::hasColumn('vee_messages', 'is_billable')) {
                $dropColumns[] = 'is_billable';
            }

            if (Schema::hasColumn('vee_messages', 'feature')) {
                $dropColumns[] = 'feature';
            }

            if (Schema::hasColumn('vee_messages', 'model')) {
                $dropColumns[] = 'model';
            }

            if (Schema::hasColumn('vee_messages', 'provider')) {
                $dropColumns[] = 'provider';
            }

            if (Schema::hasColumn('vee_messages', 'output_tokens')) {
                $dropColumns[] = 'output_tokens';
            }

            if (Schema::hasColumn('vee_messages', 'input_tokens')) {
                $dropColumns[] = 'input_tokens';
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
