<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Farmer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test Suite: Farmer Management Module
 * These tests cover the core CRUD operations for farmer records.
 */
class FarmerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_is_redirected_to_login()
    {
        $response = $this->get(route('farmers.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_can_view_farmers_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Farmer::factory()->count(5)->create();

        $response = $this->get(route('farmers.index'));
        $response->assertStatus(200);
        $response->assertViewIs('farmers.index');
        $response->assertViewHas('farmers');
    }

    /** @test */
    public function admin_can_create_a_farmer()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $farmerData = [
            'name'    => 'Ramesh Patel',
            'mobile'  => '9876543210',
            'village' => 'Anand',
            'address' => 'Near Temple, Anand',
            'aadhaar' => '123456789012',
        ];

        $response = $this->post(route('farmers.store'), $farmerData);
        $response->assertRedirect(route('farmers.index'));
        $this->assertDatabaseHas('farmers', ['name' => 'Ramesh Patel', 'mobile' => '9876543210']);
    }

    /** @test */
    public function farmer_creation_requires_name_and_mobile()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post(route('farmers.store'), []);
        $response->assertSessionHasErrors(['name', 'mobile', 'village']);
    }

    /** @test */
    public function admin_can_update_farmer()
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $farmer = Farmer::factory()->create();
        $this->actingAs($admin);

        $response = $this->put(route('farmers.update', $farmer), [
            'name'    => 'Updated Name',
            'mobile'  => $farmer->mobile,
            'village' => 'New Village',
        ]);

        $response->assertRedirect(route('farmers.show', $farmer));
        $this->assertDatabaseHas('farmers', ['id' => $farmer->id, 'name' => 'Updated Name']);
    }

    /** @test */
    public function admin_can_delete_farmer()
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $farmer = Farmer::factory()->create();
        $this->actingAs($admin);

        $response = $this->delete(route('farmers.destroy', $farmer));
        $response->assertRedirect(route('farmers.index'));
        $this->assertSoftDeleted('farmers', ['id' => $farmer->id]);
    }

    /** @test */
    public function farmer_role_cannot_access_farmer_management()
    {
        $user = User::factory()->create(['role' => 'farmer']);
        $this->actingAs($user);

        $response = $this->get(route('farmers.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function farmer_search_filters_results()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Farmer::factory()->create(['name' => 'Ramesh Patel', 'village' => 'Anand']);
        Farmer::factory()->create(['name' => 'Suresh Kumar', 'village' => 'Nadiad']);

        $response = $this->get(route('farmers.index', ['search' => 'Ramesh']));
        $response->assertStatus(200);
        $response->assertSee('Ramesh Patel');
        $response->assertDontSee('Suresh Kumar');
    }
}
