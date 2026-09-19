<?php

namespace Modules\FrontOffice\Exceptions;

use DomainException;

final class RoomUnavailableException extends DomainException
{
    public static function forRoom(string $roomNumber): self
    {
        return new self(
            "Room {$roomNumber} is unavailable for the selected period."
        );
    }
}