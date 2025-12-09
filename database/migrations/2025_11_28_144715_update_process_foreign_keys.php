<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only run this on MySQL – SQLite cannot handle any of it
        if (Schema::getConnection()->getDriverName() === 'mysql') {

            // Convert to InnoDB (only MySQL)
            DB::statement('ALTER TABLE process_progresses ENGINE=InnoDB');
            DB::statement('ALTER TABLE process_files ENGINE=InnoDB');

            // process_user: process_id → processes.id
            Schema::table('process_user', function (Blueprint $table) {
                $table->dropForeign(['process_id']);
                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });

            // tasks: process_id → processes.id
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['process_id']);
                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });

            // process_progresses: process_id → processes.id
            Schema::table('process_progresses', function (Blueprint $table) {
                $table->dropForeign('process_progresses_process_id_foreign');
                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });

            // process_files: process_id → processes.id
            Schema::table('process_files', function (Blueprint $table) {
                $table->dropIndex('process_files_process_id_foreign');
                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down(): void
    {
        // Same – only MySQL supports this
        if (Schema::getConnection()->getDriverName() === 'mysql') {

            Schema::table('process_user', function (Blueprint $table) {
                $table->dropForeign(['process_id']);
                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade');
            });

            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['process_id']);
                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade');
            });

            Schema::table('process_progresses', function (Blueprint $table) {
                $table->dropForeign('process_progresses_process_id_foreign');
                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade');
            });

            Schema::table('process_files', function (Blueprint $table) {
                $table->dropForeign(['process_id']);
                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade');
            });
        }
    }
};
