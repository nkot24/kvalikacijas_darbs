<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('task_work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('amount')->default(0); // how much was completed in this step
            $table->timestamps();
        });

        // If you truly want “shared” tasks, make user_id nullable on tasks:
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void {
        Schema::dropIfExists('task_work_logs');

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
