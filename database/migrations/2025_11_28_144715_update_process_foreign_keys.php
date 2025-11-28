<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
            $table->dropForeign(['process_id']);

            $table->foreign('process_id')
                ->references('id')
                ->on('processes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        // process_files: process_id → processes.id
        Schema::table('process_files', function (Blueprint $table) {
            $table->dropForeign(['process_id']);

            $table->foreign('process_id')
                ->references('id')
                ->on('processes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Back to only ON DELETE CASCADE (no ON UPDATE)
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
            $table->dropForeign(['process_id']);

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
};
