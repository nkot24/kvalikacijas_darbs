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
        Schema::table('order_list', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1);         
            $table->string('photo_path')->nullable();                
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); 

            $table->string('supplier_name')->nullable();             
            $table->date('ordered_at')->nullable();     
            $table->date('expected_at')->nullable();             
            $table->date('arrived_at')->nullable();                  

            $table->enum('status', ['nav pasūtīts','pasūtīts','saņemts'])->default('nav pasūtīts'); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_list', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn([
                'quantity','photo_path','created_by',
                'supplier_name','ordered_at','expected_at','arrived_at','status'
            ]);
            $table->dropSoftDeletes();
        });
    }
};
