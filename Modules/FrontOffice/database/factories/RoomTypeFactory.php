<?php

namespace Modules\FrontOffice\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\FrontOffice\Models\RoomType;

class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Standard Room',
                'Deluxe Room',
                'Suite',
            ]),

            'code' => strtoupper(fake()->unique()->lexify('???')),

            'description' => fake()->sentence(),

            'capacity' => 2,

            'base_rate' => 450000,
        ];
    }
}