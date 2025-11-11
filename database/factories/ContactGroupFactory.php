<?php

namespace Database\Factories;

use App\Enums\ModelStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactGroup>
 */
class ContactGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'user_id' => 1,
            'profile_id' => 1,
            'status' => ModelStatusEnum::PUBLISHED,
        ];
    }
}
