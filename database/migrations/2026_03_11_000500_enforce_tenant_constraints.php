<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $missingExhibitions = DB::table('exhibitions')->whereNull('tenant_id')->count();
        if ($missingExhibitions > 0) {
            throw new RuntimeException("Impossibile applicare NOT NULL su exhibitions.tenant_id: {$missingExhibitions} record senza tenant_id.");
        }

        $missingContacts = DB::table('contacts')->whereNull('tenant_id')->count();
        if ($missingContacts > 0) {
            throw new RuntimeException("Impossibile applicare NOT NULL su contacts.tenant_id: {$missingContacts} record senza tenant_id.");
        }

        Schema::table('exhibitions', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'date']);
            $table->index(['tenant_id', 'start_date']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'last_name']);
            $table->index(['tenant_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'last_name']);
            $table->dropIndex(['tenant_id', 'email']);
            $table->foreignId('tenant_id')->nullable()->change();
        });

        Schema::table('exhibitions', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'date']);
            $table->dropIndex(['tenant_id', 'start_date']);
            $table->foreignId('tenant_id')->nullable()->change();
        });
    }
};
