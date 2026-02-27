<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $exhibition = Exhibition::create([
            'user_id' => $user->id,
            'name' => 'Fiera Milano Tech',
            'date' => now()->addDays(10)->toDateString(),
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'company' => 'Acme SRL',
        ]);

        Contact::create([
            'exhibition_id' => $exhibition->id,
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => 'mario.rossi@example.com',
            'phone' => '+39 333 1234567',
            'company' => 'Cliente Demo',
            'note' => 'Interessato a demo prodotto.',
            'source' => 'internal',
        ]);
    }
}
