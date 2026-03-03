<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {

            // Ensure tables use InnoDB (required for FK)
            DB::statement('ALTER TABLE process_progresses ENGINE=InnoDB');
            DB::statement('ALTER TABLE process_files ENGINE=InnoDB');

            // process_user
            Schema::table('process_user', function (Blueprint $table) {
                $table->dropForeign(['process_id']);

                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });

            // tasks
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['process_id']);

                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });

            // process_progresses
            Schema::table('process_progresses', function (Blueprint $table) {
                $table->dropForeign(['process_id']);

                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });

            // process_files
            Schema::table('process_files', function (Blueprint $table) {
                $table->dropForeign(['process_id']);

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
        if (Schema::getConnection()->getDriverName() === 'mysql') {

            // process_user
            Schema::table('process_user', function (Blueprint $table) {
                $table->dropForeign(['process_id']);

                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade');
            });

            // tasks
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['process_id']);

                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade');
            });

            // process_progresses
            Schema::table('process_progresses', function (Blueprint $table) {
                $table->dropForeign(['process_id']);

                $table->foreign('process_id')
                    ->references('id')
                    ->on('processes')
                    ->onDelete('cascade');
            });

            // process_files
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