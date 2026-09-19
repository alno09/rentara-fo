<?php

namespace Modules\FrontOffice\Exceptions;

use DomainException;
use Modules\FrontOffice\Enums\ReservationStatus;

final class InvalidReservationStateException extends DomainException
{
    public static function expected(
        ReservationStatus $expected,
        ReservationStatus $actual,
    ): self {
        return new self(
            "Reservation must be {$expected->value}; current status is {$actual->value}."
        );
    }
}