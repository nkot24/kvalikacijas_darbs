<?php
// database/migrations/xxxx_xx_xx_xxxxxx_alter_spent_time_to_hours_in_process_progresses.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('process_progresses', function (Blueprint $table) {
            $table->decimal('spent_time', 6, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('process_progresses', function (Blueprint $table) {
            $table->unsignedInteger('spent_time')->nullable()->change();
        });
    }
};