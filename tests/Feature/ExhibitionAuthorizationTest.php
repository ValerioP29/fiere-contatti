<?php

namespace Tests\Feature;

use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExhibitionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_own_exhibitions(): void
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

    public function test_user_cannot_view_or_update_other_user_exhibition(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $otherExhibition = Exhibition::create([
            'user_id' => $other->id,
            'name' => 'Other Private Expo',
            'date' => now()->toDateString(),
            'company' => 'Other Inc',
        ]);

        $this->actingAs($owner)
            ->get(route('exhibitions.show', $otherExhibition))
            ->assertForbidden();

        $this->actingAs($owner)
            ->put(route('exhibitions.update', $otherExhibition), [
                'name' => 'Tampered Expo',
                'date' => now()->toDateString(),
                'company' => 'Tampered Inc',
            ])
            ->assertForbidden();
    }

    public function test_dashboard_renders_with_kpi_values(): void
    {
        $user = User::factory()->create();
        Exhibition::create([
            'user_id' => $user->id,
            'name' => 'Expo KPI',
            'date' => now()->toDateString(),
            'company' => 'Owner Inc',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Fiere create');
        $response->assertSee('Contatti raccolti');
        $response->assertSee('1');
        $response->assertSee('Expo KPI');
    }
}
