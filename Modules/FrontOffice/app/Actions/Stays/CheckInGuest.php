<?php

namespace Modules\FrontOffice\Actions\Stays;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Enums\StayStatus;
use Modules\FrontOffice\Exceptions\InvalidReservationStateException;
use Modules\FrontOffice\Exceptions\RoomUnavailableException;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\Stay;
use Modules\FrontOffice\Services\RoomAvailabilityService;

final class CheckInGuest
{
    public function __construct(
        private readonly RoomAvailabilityService $availabilityService,
    ) {
    }

    public function execute(Reservation $reservation): Stay
    {
        return DB::transaction(function () use ($reservation): Stay {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($reservation->status !== ReservationStatus::CONFIRMED) {
                throw InvalidReservationStateException::expected(
                    ReservationStatus::CONFIRMED,
                    $reservation->status,
                );
            }

            if ($reservation->room_id === null) {
                throw new InvalidArgumentException(
                    'A room must be assigned before check-in.'
                );
            }

            $room = Room::query()
                ->lockForUpdate()
                ->findOrFail($reservation->room_id);

            if ($room->status !== RoomStatus::AVAILABLE) {
                throw RoomUnavailableException::forRoom(
                    $room->room_number
                );
            }

            if (! $this->availabilityService->isAvailable(
                room: $room,
                arrivalDate: $reservation->arrival_date,
                departureDate: $reservation->departure_date,
                ignoreReservationId: $reservation->id,
            )) {
                throw RoomUnavailableException::forRoom(
                    $room->room_number
                );
            }

            if ($reservation->stay()->exists()) {
                throw new InvalidArgumentException(
                    'This reservation has already been checked in.'
                );
            }

            $stay = Stay::query()->create([
                'reservation_id' => $reservation->id,
                'guest_id' => $reservation->guest_id,
                'room_id' => $room->id,

                'checked_in_at' => now(),

                'expected_check_out_at' => $reservation
                    ->departure_date
                    ->copy()
                    ->setTime(12, 0),

                'status' => StayStatus::ACTIVE,
            ]);

            $reservation->update([
                'status' => ReservationStatus::CHECKED_IN,
            ]);

            $room->update([
                'status' => RoomStatus::OCCUPIED,
            ]);

            return $stay->refresh();
        }, attempts: 3);
    }
}