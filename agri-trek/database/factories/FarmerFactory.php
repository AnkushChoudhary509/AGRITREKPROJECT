<?php

namespace Database\Factories;

use App\Models\Farmer;
use Illuminate\Database\Eloquent\Factories\Factory;

class FarmerFactory extends Factory
{
    protected $model = Farmer::class;

    public function definition(): array
    {
        return [
            'name'         => $this->faker->name(),
            'mobile'       => '9' . $this->faker->numerify('#########'),
            'address'      => $this->faker->address(),
            'village'      => $this->faker->city(),
            'district'     => $this->faker->city(),
            'aadhaar'      => $this->faker->numerify('############'),
            'dob'          => $this->faker->dateTimeBetween('-70 years', '-18 years'),
            'bank_account' => $this->faker->bankAccountNumber(),
            'ifsc_code'    => strtoupper($this->faker->lexify('????')) . '0' . $this->faker->numerify('######'),
        ];
    }
}
