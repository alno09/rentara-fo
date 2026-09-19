<?php

namespace Modules\FrontOffice\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FrontOffice\Actions\Reservations\MarkReservationNoShow;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Exceptions\InvalidReservationStateException;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Services\RoomAvailabilityService;
use Modules\FrontOffice\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class MarkReservationNoShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_reservation_can_be_marked_no_show(): void
    {
        $room = Room::factory()->create(['status' => RoomStatus::AVAILABLE]);
        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $noShow = app(MarkReservationNoShow::class)->execute($reservation);

        $this->assertSame(ReservationStatus::NO_SHOW, $noShow->status);
        $this->assertSame(RoomStatus::AVAILABLE, $room->refresh()->status);
        $this->assertDatabaseMissing('stays', ['reservation_id' => $reservation->id]);
        $this->assertTrue(app(RoomAvailabilityService::class)->isAvailable(
            $room,
            $reservation->arrival_date,
            $reservation->departure_date,
        ));
    }

    public static function nonConfirmedStatuses(): array
    {
        return [
            'pending' => [ReservationStatus::PENDING],
            'checked in' => [ReservationStatus::CHECKED_IN],
            'checked out' => [ReservationStatus::CHECKED_OUT],
            'cancelled' => [ReservationStatus::CANCELLED],
            'already no show' => [ReservationStatus::NO_SHOW],
        ];
    }

    #[DataProvider('nonConfirmedStatuses')]
    public function test_non_confirmed_reservation_cannot_be_marked_no_show(ReservationStatus $status): void
    {
        $reservation = Reservation::factory()->create(['status' => $status]);

        $this->expectException(InvalidReservationStateException::class);

        app(MarkReservationNoShow::class)->execute($reservation);
    }
}
