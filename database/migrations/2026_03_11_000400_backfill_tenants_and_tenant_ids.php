<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $now = now();

            DB::table('users')
                ->select(['id', 'name', 'email'])
                ->orderBy('id')
                ->chunkById(200, function ($users) use ($now) {
                    foreach ($users as $user) {
                        $name = trim((string) ($user->name ?? ''));
                        $email = trim((string) ($user->email ?? ''));
                        $tenantName = $name !== '' ? $name : ($email !== '' ? $email : "Tenant #{$user->id}");

                        $tenantId = DB::table('tenants')
                            ->where('owner_user_id', $user->id)
                            ->orderBy('id')
                            ->value('id');

                        if (! $tenantId) {
                            $tenantId = DB::table('tenants')->insertGetId([
                                'name' => $tenantName,
                                'owner_user_id' => $user->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }

                        DB::table('tenant_user')->updateOrInsert(
                            [
                                'tenant_id' => $tenantId,
                                'user_id' => $user->id,
                            ],
                            [
                                'role' => 'owner',
                                'updated_at' => $now,
                                'created_at' => $now,
                            ]
                        );

                        DB::table('exhibitions')
                            ->where('user_id', $user->id)
                            ->whereNull('tenant_id')
                            ->update([
                                'tenant_id' => $tenantId,
                            ]);
                    }
                }, 'id');

            DB::table('exhibitions')
                ->select(['id', 'tenant_id'])
                ->whereNotNull('tenant_id')
                ->orderBy('id')
                ->chunkById(500, function ($exhibitions) {
                    foreach ($exhibitions as $exhibition) {
                        DB::table('contacts')
                            ->where('exhibition_id', $exhibition->id)
                            ->whereNull('tenant_id')
                            ->update([
                                'tenant_id' => $exhibition->tenant_id,
                            ]);
                    }
                }, 'id');

            $orphanExhibitions = DB::table('exhibitions')->whereNull('tenant_id')->count();
            if ($orphanExhibitions > 0) {
                throw new RuntimeException("Backfill bloccato: {$orphanExhibitions} exhibitions senza tenant_id.");
            }

            $orphanContacts = DB::table('contacts')->whereNull('tenant_id')->count();
            if ($orphanContacts > 0) {
                throw new RuntimeException("Backfill bloccato: {$orphanContacts} contacts senza tenant_id.");
            }
        });
    }

    public function down(): void
    {
        // No-op non distruttivo: i dati tenant creati/backfillati non vengono cancellati automaticamente.
        // Il rollback strutturale è demandato alle migration schema (drop colonne/tabelle).
    }
};
