<?php

namespace Modules\FrontOffice\Actions\Reservations;

use Illuminate\Support\Facades\DB;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Exceptions\InvalidReservationStateException;
use Modules\FrontOffice\Exceptions\RoomUnavailableException;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Services\RoomAvailabilityService;

final class ConfirmReservation
{
    public function __construct(
        private readonly RoomAvailabilityService $availabilityService,
    ) {
    }

    public function execute(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation): Reservation {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($reservation->status !== ReservationStatus::PENDING) {
                throw InvalidReservationStateException::expected(
                    ReservationStatus::PENDING,
                    $reservation->status,
                );
            }

            if ($reservation->room_id !== null) {
                $room = Room::query()
                    ->lockForUpdate()
                    ->findOrFail($reservation->room_id);

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
            }

            $reservation->update([
                'status' => ReservationStatus::CONFIRMED,
            ]);

            return $reservation->refresh();
        }, attempts: 3);
    }
}