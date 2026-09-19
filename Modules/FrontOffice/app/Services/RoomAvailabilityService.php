<?php

namespace Modules\FrontOffice\Services;

use Carbon\CarbonInterface;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;

final class RoomAvailabilityService
{
    public function isAvailable(
        Room $room,
        CarbonInterface $arrivalDate,
        CarbonInterface $departureDate,
        ?int $ignoreReservationId = null,
    ): bool {
        if ($room->status === RoomStatus::MAINTENANCE) {
            return false;
        }

        return ! Reservation::query()
            ->where('room_id', $room->id)
            ->whereIn('status', [
                ReservationStatus::PENDING->value,
                ReservationStatus::CONFIRMED->value,
                ReservationStatus::CHECKED_IN->value,
            ])
            ->when(
                $ignoreReservationId,
                fn ($query) => $query->whereKeyNot($ignoreReservationId)
            )
            ->where('arrival_date', '<', $departureDate->toDateString())
            ->where('departure_date', '>', $arrivalDate->toDateString())
            ->exists();
    }
}