<?php

namespace Modules\FrontOffice\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\FrontOffice\Models\Guest;

class GuestFactory extends Factory
{
    protected $model = Guest::class;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),

            'identity_type' => 'passport',
            'identity_number' => fake()->unique()->numerify(
                'ID########'
            ),

            'nationality' => 'Indonesia',

            'address' => fake()->address(),

            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),

            'notes' => null,
        ];
    }
}