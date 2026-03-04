<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExhibitionDashboardAndShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_all_main_exhibition_actions(): void
    {
        $user = User::factory()->create();

        $exhibition = Exhibition::create([
            'user_id' => $user->id,
            'name' => 'Web Summit',
            'date' => now()->toDateString(),
            'company' => 'Acme',
            'public_token' => (string) Str::ulid(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Web Summit');
        $response->assertSee(route('exhibitions.show', $exhibition), false);
        $response->assertSee(route('exhibitions.show', ['exhibition' => $exhibition, 'open' => 'create']), false);
        $response->assertDontSee(route('exhibitions.contacts.store', $exhibition), false);
        $response->assertSee('Aggiungi contatto');
        $response->assertSee('Dettagli');
        $response->assertSee('Modifica fiera');
        $response->assertSee('Elimina fiera');
        $response->assertSee('Condividi link');
    }


    public function test_can_create_contact_inside_exhibition_and_redirect_back_to_show(): void
    {
        $user = User::factory()->create();

        $exhibition = Exhibition::create([
            'user_id' => $user->id,
            'name' => 'Sales Expo',
            'date' => now()->toDateString(),
            'company' => 'Acme',
            'public_token' => (string) Str::ulid(),
        ]);

        $response = $this->actingAs($user)->post(route('exhibitions.contacts.store', $exhibition), [
            'first_name' => 'Anna',
            'last_name' => 'Bianchi',
            'email' => 'anna@example.com',
        ]);

        $response->assertRedirect(route('exhibitions.show', $exhibition));
        $response->assertSessionHas('status', 'Contatto aggiunto.');

        $this->assertDatabaseHas('contacts', [
            'exhibition_id' => $exhibition->id,
            'first_name' => 'Anna',
            'last_name' => 'Bianchi',
            'source' => 'internal',
        ]);
    }

    public function test_show_page_displays_only_contacts_for_selected_exhibition(): void
    {
        $user = User::factory()->create();

        $target = Exhibition::create([
            'user_id' => $user->id,
            'name' => 'Target Expo',
            'date' => now()->toDateString(),
            'company' => 'Acme',
            'public_token' => (string) Str::ulid(),
        ]);

        $other = Exhibition::create([
            'user_id' => $user->id,
            'name' => 'Other Expo',
            'date' => now()->addDay()->toDateString(),
            'company' => 'Acme',
            'public_token' => (string) Str::ulid(),
        ]);

        Contact::create([
            'exhibition_id' => $target->id,
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => 'mario@example.com',
            'source' => 'internal',
        ]);

        Contact::create([
            'exhibition_id' => $other->id,
            'first_name' => 'Luigi',
            'last_name' => 'Verdi',
            'email' => 'luigi@example.com',
            'source' => 'internal',
        ]);

        $response = $this->actingAs($user)->get(route('exhibitions.show', $target));

        $response->assertOk();
        $response->assertSee('Target Expo');
        $response->assertSee('Mario Rossi');
        $response->assertDontSee('Luigi Verdi');
        $response->assertSee('Contatti raccolti');
    }
}
