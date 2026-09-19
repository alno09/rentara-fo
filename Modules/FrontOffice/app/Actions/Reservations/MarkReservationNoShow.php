<?php

namespace Modules\FrontOffice\Actions\Reservations;

use Illuminate\Support\Facades\DB;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Exceptions\InvalidReservationStateException;
use Modules\FrontOffice\Models\Reservation;

final class MarkReservationNoShow
{
    public function execute(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation): Reservation {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($reservation->status !== ReservationStatus::CONFIRMED) {
                throw InvalidReservationStateException::expected(
                    ReservationStatus::CONFIRMED,
                    $reservation->status,
                );
            }

            $reservation->update([
                'status' => ReservationStatus::NO_SHOW,
            ]);

            return $reservation->refresh();
        }, attempts: 3);
    }
}
