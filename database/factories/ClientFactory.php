<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\InterestLevel;
use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '9'.fake()->numerify('#########'),
            'email' => fake()->optional()->safeEmail(),
            'address' => fake()->address(),
            'package_id' => Package::inRandomOrder()->value('id'),
            'interest_level_id' => InterestLevel::inRandomOrder()->value('id'),
            'status' => Client::STATUS_NEW,
            'created_by' => User::role('counsellor')->inRandomOrder()->value('id'),
        ];
    }
}
