<?php

namespace Modules\FrontOffice\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FrontOffice\Actions\Reservations\CancelReservation;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Exceptions\InvalidReservationStateException;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Services\RoomAvailabilityService;
use Modules\FrontOffice\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CancelReservationTest extends TestCase
{
    use RefreshDatabase;

    public static function cancellableStatuses(): array
    {
        return [
            'pending' => [ReservationStatus::PENDING],
            'confirmed' => [ReservationStatus::CONFIRMED],
        ];
    }

    #[DataProvider('cancellableStatuses')]
    public function test_active_reservation_can_be_cancelled(ReservationStatus $status): void
    {
        $room = Room::factory()->create(['status' => RoomStatus::AVAILABLE]);
        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
            'status' => $status,
        ]);

        $cancelled = app(CancelReservation::class)->execute($reservation);

        $this->assertSame(ReservationStatus::CANCELLED, $cancelled->status);
        $this->assertSame(RoomStatus::AVAILABLE, $room->refresh()->status);
        $this->assertTrue(app(RoomAvailabilityService::class)->isAvailable(
            $room,
            $reservation->arrival_date,
            $reservation->departure_date,
        ));
    }

    public static function nonCancellableStatuses(): array
    {
        return [
            'checked in' => [ReservationStatus::CHECKED_IN],
            'checked out' => [ReservationStatus::CHECKED_OUT],
            'already cancelled' => [ReservationStatus::CANCELLED],
            'no show' => [ReservationStatus::NO_SHOW],
        ];
    }

    #[DataProvider('nonCancellableStatuses')]
    public function test_terminal_or_checked_in_reservation_cannot_be_cancelled(ReservationStatus $status): void
    {
        $reservation = Reservation::factory()->create(['status' => $status]);

        try {
            app(CancelReservation::class)->execute($reservation);
            $this->fail('Expected an invalid reservation state.');
        } catch (InvalidReservationStateException $exception) {
            $this->assertStringContainsString($status->value, $exception->getMessage());
        }

        $this->assertSame($status, $reservation->refresh()->status);
    }
}
