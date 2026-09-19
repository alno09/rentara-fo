<?php

namespace Modules\FrontOffice\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\RoomType;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),

            'room_number' => (string) fake()->unique()->numberBetween(
                100,
                999
            ),

            'floor' => fake()->numberBetween(1, 10),

            'status' => RoomStatus::AVAILABLE,

            'notes' => null,
        ];
    }
}