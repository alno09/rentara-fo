<?php

namespace Modules\FrontOffice\Actions\Reservations;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\FrontOffice\Data\CreateReservationData;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Exceptions\RoomUnavailableException;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\RoomType;
use Modules\FrontOffice\Services\RoomAvailabilityService;

final class CreateReservation
{
    public function __construct(
        private readonly RoomAvailabilityService $availabilityService,
    ) {
    }

    public function execute(CreateReservationData $data): Reservation
    {
        $this->validateDates($data);

        return DB::transaction(function () use ($data): Reservation {
            $roomType = RoomType::query()->findOrFail($data->roomTypeId);

            $totalGuests = $data->adultCount + $data->childCount;

            if ($totalGuests > $roomType->capacity) {
                throw new InvalidArgumentException(
                    'Guest count exceeds room type capacity.'
                );
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
                )) {
                    throw RoomUnavailableException::forRoom(
                        $room->room_number
                    );
                }
            }

            return Reservation::query()->create([
                'reservation_number' => $this->generateReservationNumber(),

                'guest_id' => $data->guestId,
                'room_type_id' => $data->roomTypeId,
                'room_id' => $data->roomId,

                'arrival_date' => $data->arrivalDate,
                'departure_date' => $data->departureDate,

                'adult_count' => $data->adultCount,
                'child_count' => $data->childCount,

                'nightly_rate' => $data->nightlyRate,

                'status' => ReservationStatus::PENDING,

                'source' => $data->source,
                'notes' => $data->notes,
            ]);
        }, attempts: 3);
    }

    private function validateDates(CreateReservationData $data): void
    {
        if ($data->departureDate->lessThanOrEqualTo($data->arrivalDate)) {
            throw new InvalidArgumentException(
                'Departure date must be after arrival date.'
            );
        }
    }

    private function generateReservationNumber(): string
    {
        return 'RSV-' . Str::ulid();
    }
}