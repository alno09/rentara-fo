<?php

namespace Modules\FrontOffice\Actions\Reservations;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\FrontOffice\Data\UpdateReservationData;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Exceptions\InvalidReservationStateException;
use Modules\FrontOffice\Exceptions\RoomUnavailableException;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\RoomType;
use Modules\FrontOffice\Services\RoomAvailabilityService;

final class UpdateReservation
{
    public function __construct(
        private readonly RoomAvailabilityService $availabilityService,
    ) {
    }

    public function execute(Reservation $reservation, UpdateReservationData $data): Reservation
    {
        return DB::transaction(function () use ($reservation, $data): Reservation {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if (! in_array($reservation->status, [
                ReservationStatus::PENDING,
                ReservationStatus::CONFIRMED,
            ], true)) {
                throw new InvalidReservationStateException(
                    "Cannot edit a {$reservation->status->value} reservation."
                );
            }

            if ($data->departureDate->lessThanOrEqualTo($data->arrivalDate)) {
                throw new InvalidArgumentException('Departure date must be after arrival date.');
            }

            $roomType = RoomType::query()->findOrFail($data->roomTypeId);

            if ($data->adultCount + $data->childCount > $roomType->capacity) {
                throw new InvalidArgumentException('Guest count exceeds room type capacity.');
            }

            if ($data->roomId !== null) {
                $room = Room::query()
                    ->lockForUpdate()
                    ->findOrFail($data->roomId);

                if ($room->room_type_id !== $roomType->id) {
                    throw new InvalidArgumentException(
                        'Selected room does not belong to the selected room type.'
                    );
                }

                if (! $this->availabilityService->isAvailable(
                    room: $room,
                    arrivalDate: $data->arrivalDate,
                    departureDate: $data->departureDate,
                    ignoreReservationId: $reservation->id,
                )) {
                    throw RoomUnavailableException::forRoom($room->room_number);
                }
            }

            $reservation->update([
                'guest_id' => $data->guestId,
                'room_type_id' => $data->roomTypeId,
                'room_id' => $data->roomId,
                'arrival_date' => $data->arrivalDate,
                'departure_date' => $data->departureDate,
                'adult_count' => $data->adultCount,
                'child_count' => $data->childCount,
                'nightly_rate' => $data->nightlyRate,
                'source' => $data->source,
                'notes' => $data->notes,
            ]);

            return $reservation->refresh();
        }, attempts: 3);
    }
}
