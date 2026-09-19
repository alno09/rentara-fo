<?php

namespace Modules\FrontOffice\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Models\Guest;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\RoomType;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $arrival = today()->addDays(1);
        $departure = $arrival->copy()->addDays(2);

        return [
            'reservation_number' => 'RSV-' . Str::ulid(),

            'guest_id' => Guest::factory(),

            'room_type_id' => RoomType::factory(),

            'room_id' => null,

            'arrival_date' => $arrival,
            'departure_date' => $departure,

            'adult_count' => 1,
            'child_count' => 0,

            'nightly_rate' => 450000,

            'status' => ReservationStatus::PENDING,

            'source' => 'walk_in',

            'notes' => null,
        ];
    }
}
