<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->unsignedSmallInteger('lunch_minutes')->default(0)->after('hours_worked');
            $table->unsignedSmallInteger('break_count')->default(0)->after('lunch_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropColumn(['lunch_minutes','break_count']);
        });
    }
};