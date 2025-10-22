<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_scans', function (Blueprint $table) {
            $table->id();
            $table->string('svitr_kods');       // Scanned QR or barcode text
            $table->integer('qty')->default(1); // Quantity
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('accounted')->default(false);
            $table->timestamp('accounted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_scans');
    }
};
