<?php

namespace Modules\FrontOffice\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Models\Guest;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\RoomType;
use Modules\FrontOffice\Services\RoomAvailabilityService;
use Modules\FrontOffice\Tests\TestCase;

class RoomAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_is_available_without_reservations(): void
    {
        $room = Room::factory()->create();

        $service = app(RoomAvailabilityService::class);

        $available = $service->isAvailable(
            room: $room,
            arrivalDate: CarbonImmutable::parse('2026-09-23'),
            departureDate: CarbonImmutable::parse('2026-09-25'),
        );

        $this->assertTrue($available);
    }

    public function test_room_is_unavailable_when_dates_overlap(): void
    {
        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
        ]);

        $guest = Guest::factory()->create();

        Reservation::factory()->create([
            'guest_id' => $guest->id,
            'room_type_id' => $roomType->id,
            'room_id' => $room->id,
            'arrival_date' => '2026-09-20',
            'departure_date' => '2026-09-23',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $service = app(RoomAvailabilityService::class);

        $available = $service->isAvailable(
            room: $room,
            arrivalDate: CarbonImmutable::parse('2026-09-21'),
            departureDate: CarbonImmutable::parse('2026-09-24'),
        );

        $this->assertFalse($available);
    }

    public function test_back_to_back_reservations_are_allowed(): void
    {
        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
        ]);

        $guest = Guest::factory()->create();

        Reservation::factory()->create([
            'guest_id' => $guest->id,
            'room_type_id' => $roomType->id,
            'room_id' => $room->id,
            'arrival_date' => '2026-09-20',
            'departure_date' => '2026-09-23',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $service = app(RoomAvailabilityService::class);

        $available = $service->isAvailable(
            room: $room,
            arrivalDate: CarbonImmutable::parse('2026-09-23'),
            departureDate: CarbonImmutable::parse('2026-09-25'),
        );

        $this->assertTrue($available);
    }
}