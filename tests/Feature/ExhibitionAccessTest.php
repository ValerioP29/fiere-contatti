<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExhibitionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_only_sees_own_exhibitions(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        Exhibition::create([
            'user_id' => $owner->id,
            'name' => 'Owner Expo',
            'date' => now()->toDateString(),
            'company' => 'Owner Inc',
        ]);

        Exhibition::create([
            'user_id' => $other->id,
            'name' => 'Other Expo',
            'date' => now()->toDateString(),
            'company' => 'Other Inc',
        ]);

        $response = $this->actingAs($owner)->get(route('exhibitions.index'));

        $response->assertOk();
        $response->assertSee('Owner Expo');
        $response->assertDontSee('Other Expo');
    }

    public function test_exhibition_accepts_date_range(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('exhibitions.store'), [
            'name' => 'Range Expo',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-03',
            'company' => 'Owner Inc',
        ]);

        $response->assertRedirect(route('exhibitions.index'));
        $this->assertDatabaseHas('exhibitions', [
            'user_id' => $owner->id,
            'name' => 'Range Expo',
            'date' => '2026-03-01',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-03',
        ]);
    }

    public function test_user_cannot_open_other_user_contacts(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $exhibition = Exhibition::create([
            'user_id' => $other->id,
            'name' => 'Private Expo',
            'date' => now()->toDateString(),
            'company' => 'Other Inc',
        ]);

        $response = $this->actingAs($owner)->get(route('contacts.index', $exhibition));

        $response->assertNotFound();
    }

    public function test_user_cannot_export_other_user_contacts(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $exhibition = Exhibition::create([
            'user_id' => $other->id,
            'name' => 'Private Expo',
            'date' => now()->toDateString(),
            'company' => 'Other Inc',
        ]);

        $response = $this->actingAs($owner)->get(route('contacts.export', $exhibition));

        $response->assertNotFound();
    }

    public function test_owner_can_export_excel_only_current_exhibition_contacts(): void
    {
        $owner = User::factory()->create();
        $exhibitionA = Exhibition::create([
            'user_id' => $owner->id,
            'name' => 'Expo A',
            'date' => now()->toDateString(),
            'company' => 'Owner Inc',
        ]);
        $exhibitionB = Exhibition::create([
            'user_id' => $owner->id,
            'name' => 'Expo B',
            'date' => now()->addDay()->toDateString(),
            'company' => 'Owner Inc',
        ]);

        Contact::create([
            'exhibition_id' => $exhibitionA->id,
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => 'mario@example.com',
            'source' => 'internal',
        ]);
        Contact::create([
            'exhibition_id' => $exhibitionB->id,
            'first_name' => 'Luigi',
            'last_name' => 'Verdi',
            'email' => 'luigi@example.com',
            'source' => 'internal',
        ]);

        $response = $this->actingAs($owner)->get(route('contacts.export', $exhibitionA));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->assertSee('Mario');
        $response->assertDontSee('Luigi');
    }

    public function test_public_link_can_be_regenerated(): void
    {
        $owner = User::factory()->create();
        $exhibition = Exhibition::create([
            'user_id' => $owner->id,
            'name' => 'Token Expo',
            'date' => now()->toDateString(),
            'company' => 'Owner Inc',
            'public_token' => (string) Str::ulid(),
        ]);

        $oldToken = $exhibition->public_token;

        $response = $this->actingAs($owner)->postJson(route('exhibitions.public-link', $exhibition), [
            'regenerate' => true,
        ]);

        $response->assertOk();
        $this->assertNotEquals($oldToken, $exhibition->fresh()->public_token);
    }
}
