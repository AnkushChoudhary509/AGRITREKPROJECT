<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Drone;
use App\Models\DroneLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test Suite: Drone Monitoring Module
 * Covers drone CRUD, telemetry logs, and API endpoints.
 */
class DroneTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_drone_monitoring_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('drones.index'));
        $response->assertStatus(200);
        $response->assertViewIs('drones.index');
    }

    /** @test */
    public function admin_can_register_a_new_drone()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post(route('drones.store'), [
            'name'     => 'AgriHawk-Test',
            'drone_id' => 'DRONE-TEST-001',
            'model'    => 'DJI Agras T30',
            'status'   => 'idle',
        ]);

        $response->assertRedirect(route('drones.index'));
        $this->assertDatabaseHas('drones', ['drone_id' => 'DRONE-TEST-001']);
    }

    /** @test */
    public function api_accepts_telemetry_data()
    {
        $drone = Drone::factory()->create(['drone_id' => 'DRONE-API-01', 'status' => 'idle']);

        $response = $this->postJson("/api/drones/DRONE-API-01/log", [
            'latitude'  => 23.5120,
            'longitude' => 72.4900,
            'speed'     => 45.5,
            'altitude'  => 80.0,
            'direction' => 180,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('drone_logs', ['drone_id' => $drone->id, 'speed' => 45.5]);
        $this->assertDatabaseHas('drones', ['id' => $drone->id, 'status' => 'active']);
    }

    /** @test */
    public function api_rejects_invalid_telemetry()
    {
        Drone::factory()->create(['drone_id' => 'DRONE-API-02']);

        $response = $this->postJson("/api/drones/DRONE-API-02/log", [
            'latitude'  => 999,   // invalid
            'longitude' => 72.49,
            'speed'     => -10,   // invalid
            'altitude'  => 80,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function api_returns_drone_list()
    {
        Drone::factory()->count(3)->create();

        $response = $this->getJson('/api/drones');
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'data']);
    }

    /** @test */
    public function api_returns_drone_path_as_geojson()
    {
        $drone = Drone::factory()->create(['drone_id' => 'DRONE-PATH-01']);
        DroneLog::factory()->count(5)->create(['drone_id' => $drone->id]);

        $response = $this->getJson("/api/drones/DRONE-PATH-01/path");
        $response->assertStatus(200);
        $response->assertJsonStructure(['type', 'features']);
        $this->assertEquals('FeatureCollection', $response->json('type'));
    }

    /** @test */
    public function drone_logs_page_shows_telemetry_history()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $drone = Drone::factory()->create();
        DroneLog::factory()->count(10)->create(['drone_id' => $drone->id]);
        $this->actingAs($admin);

        $response = $this->get(route('drones.logs', $drone));
        $response->assertStatus(200);
        $response->assertViewIs('drones.logs');
    }
}
