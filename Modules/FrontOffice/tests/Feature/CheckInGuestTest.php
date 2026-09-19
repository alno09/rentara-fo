<?php

namespace Modules\FrontOffice\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FrontOffice\Actions\Stays\CheckInGuest;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Enums\StayStatus;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\RoomType;
use Modules\FrontOffice\Tests\TestCase;
use Modules\FrontOffice\Exceptions\InvalidReservationStateException;

class CheckInGuestTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_reservation_can_be_checked_in(): void
    {
        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::AVAILABLE,
        ]);

        $reservation = Reservation::factory()->create([
            'room_type_id' => $roomType->id,

            'room_id' => $room->id,

            'arrival_date' => today(),
            'departure_date' => today()->addDays(2),

            'status' => ReservationStatus::CONFIRMED,
        ]);

        $stay = app(CheckInGuest::class)
            ->execute($reservation);

        $this->assertDatabaseHas('stays', [
            'id' => $stay->id,
            'reservation_id' => $reservation->id,
            'room_id' => $room->id,
            'status' => StayStatus::ACTIVE->value,
        ]);

        $this->assertSame(
            StayStatus::ACTIVE,
            $stay->status
        );

        $this->assertSame(
            ReservationStatus::CHECKED_IN,
            $reservation->refresh()->status
        );

        $this->assertSame(
            RoomStatus::OCCUPIED,
            $room->refresh()->status
        );
    }

    public function test_reservation_cannot_be_checked_in_twice(): void
    {
        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
        ]);

        $reservation = Reservation::factory()->create([
            'room_type_id' => $roomType->id,
            'room_id' => $room->id,

            'arrival_date' => today(),
            'departure_date' => today()->addDays(2),

            'status' => ReservationStatus::CONFIRMED,
        ]);

        $action = app(CheckInGuest::class);

        $action->execute($reservation);

        $this->expectException(
            InvalidReservationStateException::class
        );

        $action->execute(
            $reservation->refresh()
        );
    }
}
