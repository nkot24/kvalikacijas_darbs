<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained()->onDelete('cascade');
            $table->foreignId('process_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['nav uzsākts', 'daļēji pabeigts', 'pabeigts'])->default('nav uzsākts');
            $table->integer('done_amount')->nullable(); // e.g. 20 grills
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tasks');
    }
};
