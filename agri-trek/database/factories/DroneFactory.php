<?php

namespace Database\Factories;

use App\Models\Drone;
use Illuminate\Database\Eloquent\Factories\Factory;

class DroneFactory extends Factory
{
    protected $model = Drone::class;

    public function definition(): array
    {
        $models = ['DJI Agras T30', 'DJI Phantom 4', 'Parrot Bluegrass', 'senseFly eBee', 'AgEagle RX-60'];

        return [
            'name'        => 'Drone-' . strtoupper($this->faker->lexify('???')),
            'drone_id'    => 'DRONE-' . $this->faker->unique()->numerify('####'),
            'model'       => $this->faker->randomElement($models),
            'status'      => $this->faker->randomElement(['active', 'idle', 'offline']),
            'description' => 'Agricultural surveillance drone for crop monitoring.',
        ];
    }

    public function active():  static { return $this->state(['status' => 'active']); }
    public function idle():    static { return $this->state(['status' => 'idle']); }
    public function offline(): static { return $this->state(['status' => 'offline']); }
}
