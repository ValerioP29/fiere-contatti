<?php

namespace Tests\Feature;

use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_form_can_store_contact(): void
    {
        $user = User::factory()->create();
        $exhibition = Exhibition::create([
            'user_id' => $user->id,
            'name' => 'Public Expo',
            'date' => now()->toDateString(),
            'company' => 'Acme',
            'public_token' => Str::random(48),
        ]);

        $response = $this->post(route('public.store', $exhibition->public_token), [
            'first_name' => 'Luigi',
            'last_name' => 'Verdi',
            'email' => 'luigi@example.com',
        ]);

        $response->assertRedirect(route('public.thanks', $exhibition->public_token));
        $this->assertDatabaseHas('contacts', [
            'exhibition_id' => $exhibition->id,
            'first_name' => 'Luigi',
            'source' => 'public',
        ]);
    }
}
