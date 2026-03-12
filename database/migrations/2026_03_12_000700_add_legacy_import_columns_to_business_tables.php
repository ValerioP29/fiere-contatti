<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->string('legacy_source', 32)->nullable()->after('public_token');
            $table->unsignedBigInteger('legacy_id')->nullable()->after('legacy_source');
            $table->timestamp('legacy_updated_at')->nullable()->after('legacy_id');
            $table->foreignId('import_batch_id')->nullable()->after('legacy_updated_at')->constrained('import_batches')->nullOnDelete();

            // Idempotency key for known legacy schema (tenant + source + legacy PK).
            $table->unique(['tenant_id', 'legacy_source', 'legacy_id'], 'exhibitions_tenant_legacy_unique');
            $table->index(['import_batch_id', 'legacy_source']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('legacy_source', 32)->nullable()->after('source');
            $table->unsignedBigInteger('legacy_id')->nullable()->after('legacy_source');
            $table->timestamp('legacy_updated_at')->nullable()->after('legacy_id');
            $table->foreignId('import_batch_id')->nullable()->after('legacy_updated_at')->constrained('import_batches')->nullOnDelete();

            // Idempotency key for known legacy schema (tenant + source + legacy PK).
            $table->unique(['tenant_id', 'legacy_source', 'legacy_id'], 'contacts_tenant_legacy_unique');
            $table->index(['import_batch_id', 'legacy_source']);
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropUnique('contacts_tenant_legacy_unique');
            $table->dropIndex(['import_batch_id', 'legacy_source']);
            $table->dropConstrainedForeignId('import_batch_id');
            $table->dropColumn(['legacy_source', 'legacy_id', 'legacy_updated_at']);
        });

        Schema::table('exhibitions', function (Blueprint $table) {
            $table->dropUnique('exhibitions_tenant_legacy_unique');
            $table->dropIndex(['import_batch_id', 'legacy_source']);
            $table->dropConstrainedForeignId('import_batch_id');
            $table->dropColumn(['legacy_source', 'legacy_id', 'legacy_updated_at']);
        });
    }
};
