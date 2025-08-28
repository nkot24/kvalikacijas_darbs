<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('process_progresses', function (Blueprint $table) {
            // If your table is named `process_progress`, adjust the name below
            if (!Schema::hasColumn('process_progresses', 'task_id')) {
                $table->foreignId('task_id')
                      ->nullable()
                      ->after('process_id')
                      ->constrained('tasks')
                      ->nullOnDelete()
                      ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('process_progresses', function (Blueprint $table) {
            if (Schema::hasColumn('process_progresses', 'task_id')) {
                $table->dropConstrainedForeignId('task_id');
            }
        });
    }
};
