<?php

namespace Modules\FrontOffice\Actions\Reservations;

use Illuminate\Support\Facades\DB;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Exceptions\InvalidReservationStateException;
use Modules\FrontOffice\Models\Reservation;

final class CancelReservation
{
    public function execute(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation): Reservation {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if (! in_array($reservation->status, [
                ReservationStatus::PENDING,
                ReservationStatus::CONFIRMED,
            ], true)) {
                throw new InvalidReservationStateException(
                    "Cannot cancel a {$reservation->status->value} reservation."
                );
            }

            $reservation->update([
                'status' => ReservationStatus::CANCELLED,
            ]);

            return $reservation->refresh();
        }, attempts: 3);
    }
}
