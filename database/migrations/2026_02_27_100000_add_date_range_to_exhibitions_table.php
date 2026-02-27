<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('date');
            $table->date('end_date')->nullable()->after('start_date');
            $table->index(['user_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'start_date']);
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
