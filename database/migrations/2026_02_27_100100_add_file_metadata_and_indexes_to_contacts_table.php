<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('business_card_path');
            $table->string('file_original_name')->nullable()->after('file_path');
            $table->string('file_mime', 120)->nullable()->after('file_original_name');
            $table->unsignedBigInteger('file_size')->nullable()->after('file_mime');

            $table->index(['exhibition_id', 'phone']);
            $table->index(['exhibition_id', 'company']);
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['exhibition_id', 'phone']);
            $table->dropIndex(['exhibition_id', 'company']);
            $table->dropColumn(['file_path', 'file_original_name', 'file_mime', 'file_size']);
        });
    }
};
