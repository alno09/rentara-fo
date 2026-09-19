<?php

namespace Modules\FrontOffice\Data;

use Carbon\CarbonImmutable;

final readonly class UpdateReservationData
{
    public function __construct(
        public int $guestId,
        public int $roomTypeId,
        public CarbonImmutable $arrivalDate,
        public CarbonImmutable $departureDate,
        public int $adultCount,
        public int $childCount,
        public string $nightlyRate,
        public ?int $roomId = null,
        public ?string $source = null,
        public ?string $notes = null,
    ) {
    }
}
