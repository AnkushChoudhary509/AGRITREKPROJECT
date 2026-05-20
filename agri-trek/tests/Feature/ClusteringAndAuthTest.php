<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Drone;
use App\Models\DroneLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test Suite: Authentication Module
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function valid_credentials_log_user_in()
    {
        $user = User::factory()->create([
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $response = $this->post('/login', [
            'email'    => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function invalid_credentials_are_rejected()
    {
        User::factory()->create(['email' => 'admin@test.com', 'password' => bcrypt('password')]);

        $response = $this->post('/login', [
            'email'    => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function logout_clears_session()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}

/**
 * Test Suite: K-Means Clustering Module
 */
class ClusteringTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function clustering_page_is_accessible_to_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('clustering.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function clustering_runs_with_enough_data_points()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $drone = Drone::factory()->create();

        // Create enough log points for K=4 clustering
        for ($i = 0; $i < 20; $i++) {
            DroneLog::factory()->create([
                'drone_id'  => $drone->id,
                'latitude'  => 23.5 + ($i * 0.01),
                'longitude' => 72.5 + ($i * 0.01),
            ]);
        }

        $this->actingAs($admin);

        $response = $this->post(route('clustering.run'), ['k' => 4]);
        $response->assertRedirect(route('clustering.index'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function clustering_fails_gracefully_with_insufficient_data()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $drone = Drone::factory()->create();

        // Only 2 points, requesting k=5
        DroneLog::factory()->count(2)->create(['drone_id' => $drone->id]);

        $this->actingAs($admin);

        $response = $this->post(route('clustering.run'), ['k' => 5]);
        $response->assertRedirect(route('clustering.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function k_value_is_clamped_between_2_and_10()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $drone = Drone::factory()->create();
        DroneLog::factory()->count(30)->create(['drone_id' => $drone->id]);
        $this->actingAs($admin);

        // k=1 should be clamped to 2
        $this->post(route('clustering.run'), ['k' => 1]);
        $this->assertEquals(2, session('cluster_k'));

        // k=20 should be clamped to 10
        $this->post(route('clustering.run'), ['k' => 20]);
        $this->assertEquals(10, session('cluster_k'));
    }
}
