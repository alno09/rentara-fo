<?php

namespace Modules\FrontOffice\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\FrontOffice\Actions\Reservations\UpdateReservation;
use Modules\FrontOffice\Data\UpdateReservationData;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Exceptions\InvalidReservationStateException;
use Modules\FrontOffice\Exceptions\RoomUnavailableException;
use Modules\FrontOffice\Models\Guest;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\RoomType;
use Modules\FrontOffice\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UpdateReservationTest extends TestCase
{
    use RefreshDatabase;

    public static function editableStatuses(): array
    {
        return [
            'pending' => [ReservationStatus::PENDING],
            'confirmed' => [ReservationStatus::CONFIRMED],
        ];
    }

    #[DataProvider('editableStatuses')]
    public function test_editable_reservation_can_change_dates(ReservationStatus $status): void
    {
        $room = Room::factory()->create();
        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
            'status' => $status,
        ]);

        $updated = app(UpdateReservation::class)->execute($reservation, $this->data($reservation, [
            'arrivalDate' => CarbonImmutable::parse('2026-10-20'),
            'departureDate' => CarbonImmutable::parse('2026-10-22'),
        ]));

        $this->assertSame('2026-10-20', $updated->arrival_date->toDateString());
        $this->assertSame('2026-10-22', $updated->departure_date->toDateString());
        $this->assertSame($status, $updated->status);
    }

    #[DataProvider('editableStatuses')]
    public function test_editable_reservation_can_change_room(ReservationStatus $status): void
    {
        $oldRoom = Room::factory()->create();
        $newRoom = Room::factory()->create(['room_type_id' => $oldRoom->room_type_id]);
        $reservation = Reservation::factory()->create([
            'room_type_id' => $oldRoom->room_type_id,
            'room_id' => $oldRoom->id,
            'status' => $status,
        ]);

        $updated = app(UpdateReservation::class)->execute($reservation, $this->data($reservation, [
            'roomId' => $newRoom->id,
        ]));

        $this->assertSame($newRoom->id, $updated->room_id);
        $this->assertSame(RoomStatus::AVAILABLE, $oldRoom->refresh()->status);
        $this->assertSame(RoomStatus::AVAILABLE, $newRoom->refresh()->status);
    }

    public function test_overlapping_update_is_rejected(): void
    {
        $room = Room::factory()->create();
        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
            'arrival_date' => '2026-10-20',
            'departure_date' => '2026-10-22',
        ]);
        Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
            'arrival_date' => '2026-10-22',
            'departure_date' => '2026-10-24',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $this->expectException(RoomUnavailableException::class);

        app(UpdateReservation::class)->execute($reservation, $this->data($reservation, [
            'departureDate' => CarbonImmutable::parse('2026-10-23'),
        ]));
    }

    public function test_reservation_does_not_conflict_with_itself(): void
    {
        $room = Room::factory()->create();
        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
        ]);

        $updated = app(UpdateReservation::class)->execute($reservation, $this->data($reservation));

        $this->assertSame($room->id, $updated->room_id);
    }

    public function test_back_to_back_update_is_allowed(): void
    {
        $room = Room::factory()->create();
        Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
            'arrival_date' => '2026-10-20',
            'departure_date' => '2026-10-22',
            'status' => ReservationStatus::CONFIRMED,
        ]);
        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => null,
        ]);

        $updated = app(UpdateReservation::class)->execute($reservation, $this->data($reservation, [
            'roomId' => $room->id,
            'arrivalDate' => CarbonImmutable::parse('2026-10-22'),
            'departureDate' => CarbonImmutable::parse('2026-10-24'),
        ]));

        $this->assertSame($room->id, $updated->room_id);
    }

    public function test_incompatible_room_type_is_rejected(): void
    {
        $room = Room::factory()->create();
        $otherType = RoomType::factory()->create();
        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
        ]);

        $this->expectException(InvalidArgumentException::class);

        app(UpdateReservation::class)->execute($reservation, $this->data($reservation, [
            'roomTypeId' => $otherType->id,
        ]));
    }

    public function test_capacity_overflow_is_rejected(): void
    {
        $roomType = RoomType::factory()->create(['capacity' => 2]);
        $reservation = Reservation::factory()->create(['room_type_id' => $roomType->id]);

        $this->expectException(InvalidArgumentException::class);

        app(UpdateReservation::class)->execute($reservation, $this->data($reservation, [
            'adultCount' => 3,
        ]));
    }

    public function test_departure_must_be_after_arrival(): void
    {
        $reservation = Reservation::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        app(UpdateReservation::class)->execute($reservation, $this->data($reservation, [
            'departureDate' => CarbonImmutable::parse($reservation->arrival_date),
        ]));
    }

    public function test_maintenance_room_is_unavailable(): void
    {
        $room = Room::factory()->create(['status' => RoomStatus::MAINTENANCE]);
        $reservation = Reservation::factory()->create(['room_type_id' => $room->room_type_id]);

        $this->expectException(RoomUnavailableException::class);

        app(UpdateReservation::class)->execute($reservation, $this->data($reservation, [
            'roomId' => $room->id,
        ]));
    }

    public static function nonEditableStatuses(): array
    {
        return [
            'checked in' => [ReservationStatus::CHECKED_IN],
            'checked out' => [ReservationStatus::CHECKED_OUT],
            'cancelled' => [ReservationStatus::CANCELLED],
            'no show' => [ReservationStatus::NO_SHOW],
        ];
    }

    #[DataProvider('nonEditableStatuses')]
    public function test_terminal_reservation_cannot_be_edited(ReservationStatus $status): void
    {
        $reservation = Reservation::factory()->create(['status' => $status]);

        $this->expectException(InvalidReservationStateException::class);

        app(UpdateReservation::class)->execute($reservation, $this->data($reservation));
    }

    public function test_all_editable_fields_are_saved_without_changing_status(): void
    {
        $reservation = Reservation::factory()->create();
        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['capacity' => 4]);

        $updated = app(UpdateReservation::class)->execute($reservation, $this->data($reservation, [
            'guestId' => $guest->id,
            'roomTypeId' => $roomType->id,
            'roomId' => null,
            'adultCount' => 2,
            'childCount' => 1,
            'nightlyRate' => '800000',
            'source' => 'phone',
            'notes' => 'Late arrival',
        ]));

        $this->assertSame($guest->id, $updated->guest_id);
        $this->assertSame($roomType->id, $updated->room_type_id);
        $this->assertNull($updated->room_id);
        $this->assertSame(2, $updated->adult_count);
        $this->assertSame(1, $updated->child_count);
        $this->assertSame('800000.00', $updated->nightly_rate);
        $this->assertSame('phone', $updated->source);
        $this->assertSame('Late arrival', $updated->notes);
        $this->assertSame(ReservationStatus::PENDING, $updated->status);
    }

    private function data(Reservation $reservation, array $overrides = []): UpdateReservationData
    {
        return new UpdateReservationData(
            guestId: $overrides['guestId'] ?? $reservation->guest_id,
            roomTypeId: $overrides['roomTypeId'] ?? $reservation->room_type_id,
            arrivalDate: $overrides['arrivalDate'] ?? CarbonImmutable::parse($reservation->arrival_date),
            departureDate: $overrides['departureDate'] ?? CarbonImmutable::parse($reservation->departure_date),
            adultCount: $overrides['adultCount'] ?? $reservation->adult_count,
            childCount: $overrides['childCount'] ?? $reservation->child_count,
            nightlyRate: $overrides['nightlyRate'] ?? $reservation->nightly_rate,
            roomId: array_key_exists('roomId', $overrides) ? $overrides['roomId'] : $reservation->room_id,
            source: $overrides['source'] ?? $reservation->source,
            notes: $overrides['notes'] ?? $reservation->notes,
        );
    }
}
