<?php

namespace Database\Factories;

use App\Enums\ModelStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'=> fake()->firstName(),
            'lastname'=> fake()->lastName(),
            'phone'=> fake()->phoneNumber(),
            'company'=> fake()->company(),
            'country'=> fake()->countryCode(),
            'status'=> ModelStatusEnum::PUBLISHED,
        ];
    }
}
