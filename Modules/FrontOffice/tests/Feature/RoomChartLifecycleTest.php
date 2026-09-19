<?php

namespace Modules\FrontOffice\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Enums\StayStatus;
use Modules\FrontOffice\Filament\Pages\RoomChart;
use Modules\FrontOffice\Models\Guest;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\RoomType;
use Modules\FrontOffice\Tests\TestCase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class RoomChartLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_can_complete_its_lifecycle_from_the_room_chart(): void
    {
        $room = Room::factory()->create();
        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
            'arrival_date' => today(),
            'departure_date' => today()->addDays(2),
            'status' => ReservationStatus::PENDING,
        ]);

        $chart = Livewire::test(RoomChart::class)
            ->call('openReservationDetail', $reservation->id)
            ->call('confirmSelectedReservation');

        $this->assertSame(ReservationStatus::CONFIRMED, $reservation->refresh()->status);

        $chart->call('checkInSelectedReservation');

        $this->assertSame(ReservationStatus::CHECKED_IN, $reservation->refresh()->status);
        $this->assertSame(RoomStatus::OCCUPIED, $room->refresh()->status);
        $this->assertDatabaseHas('stays', [
            'reservation_id' => $reservation->id,
            'status' => StayStatus::ACTIVE->value,
        ]);

        $chart->call('checkOutSelectedReservation');

        $this->assertSame(ReservationStatus::CHECKED_OUT, $reservation->refresh()->status);
        $this->assertSame(RoomStatus::DIRTY, $room->refresh()->status);
        $this->assertSame(StayStatus::COMPLETED, $reservation->stay->refresh()->status);
        $chart->assertSee('Checked out');
    }

    public function test_check_out_without_a_stay_does_not_change_state(): void
    {
        $room = Room::factory()->create(['status' => RoomStatus::OCCUPIED]);
        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
            'arrival_date' => today(),
            'departure_date' => today()->addDays(2),
            'status' => ReservationStatus::CHECKED_IN,
        ]);

        Livewire::test(RoomChart::class)
            ->call('openReservationDetail', $reservation->id)
            ->call('checkOutSelectedReservation');

        $this->assertSame(ReservationStatus::CHECKED_IN, $reservation->refresh()->status);
        $this->assertSame(RoomStatus::OCCUPIED, $room->refresh()->status);
        $this->assertDatabaseMissing('stays', ['reservation_id' => $reservation->id]);
    }

    public function test_room_type_and_floor_filters_limit_visible_rooms(): void
    {
        $deluxe = RoomType::factory()->create(['name' => 'Deluxe']);
        $standard = RoomType::factory()->create(['name' => 'Standard']);
        Room::factory()->create(['room_type_id' => $deluxe->id, 'room_number' => 'D101', 'floor' => 1]);
        Room::factory()->create(['room_type_id' => $deluxe->id, 'room_number' => 'D201', 'floor' => 2]);
        Room::factory()->create(['room_type_id' => $standard->id, 'room_number' => 'S101', 'floor' => 1]);

        Livewire::test(RoomChart::class)
            ->set('roomTypeFilter', $deluxe->id)
            ->set('floorFilter', 1)
            ->assertSee('D101')
            ->assertDontSee('D201')
            ->assertDontSee('S101');
    }

    public function test_quick_reservation_still_creates_a_booking(): void
    {
        $room = Room::factory()->create();
        $guest = Guest::factory()->create();
        $arrival = today()->addDay()->toDateString();

        Livewire::test(RoomChart::class)
            ->call('openReservationModal', $room->id, $arrival)
            ->assertSet('selectedRoomId', $room->id)
            ->assertSet('selectedDate', $arrival)
            ->assertSet('departureDate', today()->addDays(2)->toDateString())
            ->assertSet('nightlyRate', (string) $room->roomType->base_rate)
            ->set('guestId', $guest->id)
            ->call('createReservation');

        $this->assertDatabaseHas('reservations', [
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'arrival_date' => $arrival,
            'status' => ReservationStatus::PENDING->value,
        ]);
    }

    public function test_cancelled_reservation_stays_visible_and_room_can_be_rebooked(): void
    {
        $room = Room::factory()->create();
        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
            'arrival_date' => today(),
            'departure_date' => today()->addDay(),
            'status' => ReservationStatus::PENDING,
        ]);
        $guest = Guest::factory()->create();

        $chart = Livewire::test(RoomChart::class)
            ->call('openReservationDetail', $reservation->id)
            ->call('cancelSelectedReservation')
            ->assertSet('showReservationDetailModal', true)
            ->assertSee('Cancelled');

        $this->assertSame(ReservationStatus::CANCELLED, $reservation->refresh()->status);

        $chart->call('closeReservationDetail')
            ->call('openReservationModal', $room->id, today()->toDateString())
            ->set('guestId', $guest->id)
            ->call('createReservation');

        $this->assertDatabaseHas('reservations', [
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'status' => ReservationStatus::PENDING->value,
        ]);

        $chart->assertSee($reservation->guest->full_name)
            ->assertSee($guest->full_name);
    }

    public function test_no_show_reservation_stays_visible_and_room_can_be_rebooked(): void
    {
        $room = Room::factory()->create();
        $reservation = Reservation::factory()->create([
            'room_type_id' => $room->room_type_id,
            'room_id' => $room->id,
            'arrival_date' => today(),
            'departure_date' => today()->addDay(),
            'status' => ReservationStatus::CONFIRMED,
        ]);
        $guest = Guest::factory()->create();

        $chart = Livewire::test(RoomChart::class)
            ->call('openReservationDetail', $reservation->id)
            ->call('markSelectedReservationNoShow')
            ->assertSet('showReservationDetailModal', true)
            ->assertSee('No Show');

        $this->assertSame(ReservationStatus::NO_SHOW, $reservation->refresh()->status);
        $this->assertDatabaseMissing('stays', ['reservation_id' => $reservation->id]);

        $chart->call('closeReservationDetail')
            ->call('openReservationModal', $room->id, today()->toDateString())
            ->set('guestId', $guest->id)
            ->call('createReservation');

        $this->assertDatabaseHas('reservations', [
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'status' => ReservationStatus::PENDING->value,
        ]);

        $chart->assertSee($reservation->guest->full_name)
            ->assertSee($guest->full_name);
    }
}
