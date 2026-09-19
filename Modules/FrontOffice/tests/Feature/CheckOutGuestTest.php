<?php

namespace Modules\FrontOffice\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FrontOffice\Actions\Stays\CheckOutGuest;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Enums\StayStatus;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\Stay;
use Modules\FrontOffice\Tests\TestCase;

class CheckOutGuestTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_stay_can_be_checked_out(): void
    {
        $room = Room::factory()->create([
            'status' => RoomStatus::OCCUPIED,
        ]);

        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,

            'status' => ReservationStatus::CHECKED_IN,
        ]);

        $stay = Stay::query()->create([
            'reservation_id' => $reservation->id,

            'guest_id' => $reservation->guest_id,

            'room_id' => $room->id,

            'checked_in_at' => now()->subDay(),

            'expected_check_out_at' => now(),

            'status' => StayStatus::ACTIVE,
        ]);

        $stay = app(CheckOutGuest::class)
            ->execute($stay);

        $this->assertSame(
            StayStatus::COMPLETED,
            $stay->status
        );

        $this->assertSame(
            ReservationStatus::CHECKED_OUT,
            $reservation->refresh()->status
        );

        $this->assertSame(
            RoomStatus::DIRTY,
            $room->refresh()->status
        );

        $this->assertNotNull(
            $stay->checked_out_at
        );
    }
}