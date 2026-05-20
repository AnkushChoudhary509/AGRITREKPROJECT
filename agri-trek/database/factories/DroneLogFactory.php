<?php

namespace Database\Factories;

use App\Models\DroneLog;
use App\Models\Drone;
use Illuminate\Database\Eloquent\Factories\Factory;

class DroneLogFactory extends Factory
{
    protected $model = DroneLog::class;

    public function definition(): array
    {
        return [
            'drone_id'  => Drone::factory(),
            'latitude'  => $this->faker->latitude(23.0, 24.0),
            'longitude' => $this->faker->longitude(72.0, 73.0),
            'speed'     => $this->faker->numberBetween(20, 80),
            'altitude'  => $this->faker->numberBetween(30, 200),
            'direction' => $this->faker->numberBetween(0, 359),
        ];
    }
}
