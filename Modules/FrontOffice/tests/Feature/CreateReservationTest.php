<?php

namespace Modules\FrontOffice\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FrontOffice\Actions\Reservations\CreateReservation;
use Modules\FrontOffice\Data\CreateReservationData;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Exceptions\RoomUnavailableException;
use Modules\FrontOffice\Models\Guest;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\RoomType;
use Modules\FrontOffice\Tests\TestCase;

class CreateReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_reservation(): void
    {
        $roomType = RoomType::factory()->create([
            'capacity' => 2,
        ]);

        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
        ]);

        $guest = Guest::factory()->create();

        $data = new CreateReservationData(
            guestId: $guest->id,
            roomTypeId: $roomType->id,

            arrivalDate: CarbonImmutable::parse('2026-09-20'),
            departureDate: CarbonImmutable::parse('2026-09-22'),

            adultCount: 1,
            childCount: 0,

            nightlyRate: '450000',

            roomId: $room->id,
            source: 'walk_in',
        );

        $reservation = app(CreateReservation::class)
            ->execute($data);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'status' => ReservationStatus::PENDING->value,
        ]);
    }

    public function test_it_allows_a_reservation_starting_on_the_previous_departure_date(): void
    {
        $roomType = RoomType::factory()->create();
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
        ]);

        Reservation::factory()->create([
            'room_type_id' => $roomType->id,
            'room_id' => $room->id,
            'arrival_date' => '2026-09-20',
            'departure_date' => '2026-09-22',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $guest = Guest::factory()->create();
        $data = new CreateReservationData(
            guestId: $guest->id,
            roomTypeId: $roomType->id,
            arrivalDate: CarbonImmutable::parse('2026-09-22'),
            departureDate: CarbonImmutable::parse('2026-09-24'),
            adultCount: 1,
            childCount: 0,
            nightlyRate: '450000',
            roomId: $room->id,
        );

        $reservation = app(CreateReservation::class)->execute($data);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'room_id' => $room->id,
            'arrival_date' => '2026-09-22',
            'departure_date' => '2026-09-24',
        ]);
    }

    public function test_it_rejects_overlapping_reservation(): void
    {
        $roomType = RoomType::factory()->create();
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
        ]);

        Reservation::factory()->create([
            'room_type_id' => $roomType->id,
            'room_id' => $room->id,
            'arrival_date' => '2026-09-20',
            'departure_date' => '2026-09-22',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $guest = Guest::factory()->create();
        $data = new CreateReservationData(
            guestId: $guest->id,
            roomTypeId: $roomType->id,
            arrivalDate: CarbonImmutable::parse('2026-09-21'),
            departureDate: CarbonImmutable::parse('2026-09-23'),
            adultCount: 1,
            childCount: 0,
            nightlyRate: '450000',
            roomId: $room->id,
        );

        $this->expectException(
            RoomUnavailableException::class
        );

        app(CreateReservation::class)->execute($data);
    }
}
