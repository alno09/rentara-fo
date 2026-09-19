<?php

namespace Modules\FrontOffice\Actions\Stays;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Enums\StayStatus;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\Stay;

final class CheckOutGuest
{
    public function execute(Stay $stay): Stay
    {
        return DB::transaction(function () use ($stay): Stay {
            $stay = Stay::query()
                ->lockForUpdate()
                ->findOrFail($stay->id);

            if ($stay->status !== StayStatus::ACTIVE) {
                throw new InvalidArgumentException(
                    'Only active stays can be checked out.'
                );
            }

            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($stay->reservation_id);

            $room = Room::query()
                ->lockForUpdate()
                ->findOrFail($stay->room_id);

            $stay->update([
                'checked_out_at' => now(),
                'status' => StayStatus::COMPLETED,
            ]);

            $reservation->update([
                'status' => ReservationStatus::CHECKED_OUT,
            ]);

            $room->update([
                'status' => RoomStatus::DIRTY,
            ]);

            return $stay->refresh();
        }, attempts: 3);
    }
}