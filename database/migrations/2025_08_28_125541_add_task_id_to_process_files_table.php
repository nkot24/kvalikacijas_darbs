<?php

// database/migrations/2025_08_28_201000_add_task_id_to_process_files_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('process_files', function (Blueprint $table) {
            $table->foreignId('task_id')
                  ->nullable()
                  ->after('process_id')
                  ->constrained('tasks')
                  ->cascadeOnDelete();

            // (optional but recommended) if you want per-task uniqueness per filename:
            // $table->index(['task_id']);
        });
    }

    public function down(): void {
        Schema::table('process_files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('task_id');
        });
    }
};
