<?php

namespace Modules\FrontOffice\Filament\Pages;

use Carbon\CarbonImmutable;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use InvalidArgumentException;
use Modules\FrontOffice\Actions\Reservations\CreateReservation;
use Modules\FrontOffice\Data\CreateReservationData;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Models\Guest;
use Modules\FrontOffice\Models\Room;

class RoomChart extends Page
{
    protected static ?string $navigationLabel = 'Room Chart';

    protected static ?string $title = 'Room Chart';

    protected static string | \UnitEnum | null $navigationGroup = 'Front Office';

    protected static ?int $navigationSort = 0;

    protected string $view =
        'frontoffice::filament.pages.room-chart';

    /*
    |--------------------------------------------------------------------------
    | Chart State
    |--------------------------------------------------------------------------
    */

    public string $startDate;

    public int $days = 7;

    /*
    |--------------------------------------------------------------------------
    | Reservation Modal State
    |--------------------------------------------------------------------------
    */

    public bool $showReservationModal = false;

    public ?int $selectedRoomId = null;

    public ?string $selectedDate = null;

    public ?int $guestId = null;

    public ?string $departureDate = null;

    public int $adultCount = 1;

    public int $childCount = 0;

    public ?string $nightlyRate = null;

    public string $source = 'walk_in';

    public ?string $notes = null;

    public bool $showReservationDetailModal = false;

    public ?int $selectedReservationId = null;

    public function mount(): void
    {
        $this->startDate = now()
            ->startOfDay()
            ->toDateString();
    }

    /*
    |--------------------------------------------------------------------------
    | Chart Navigation
    |--------------------------------------------------------------------------
    */

    public function previousPeriod(): void
    {
        $this->startDate = CarbonImmutable::parse($this->startDate)
            ->subDays($this->days)
            ->toDateString();
    }

    public function nextPeriod(): void
    {
        $this->startDate = CarbonImmutable::parse($this->startDate)
            ->addDays($this->days)
            ->toDateString();
    }

    public function goToToday(): void
    {
        $this->startDate = now()->toDateString();
    }

    /*
    |--------------------------------------------------------------------------
    | Computed Data
    |--------------------------------------------------------------------------
    */

    public function getDatesProperty(): array
    {
        $start = CarbonImmutable::parse($this->startDate);

        return collect(range(0, $this->days - 1))
            ->map(
                fn (int $offset) => $start->addDays($offset)
            )
            ->all();
    }

    public function getRoomsProperty()
    {
        $start = CarbonImmutable::parse($this->startDate);

        $end = $start->addDays($this->days);

        return Room::query()
            ->with([
                'roomType',

                'reservations' => fn ($query) =>
                    $query
                        ->whereIn('status', [
                            ReservationStatus::PENDING->value,
                            ReservationStatus::CONFIRMED->value,
                            ReservationStatus::CHECKED_IN->value,
                        ])
                        ->where(
                            'arrival_date',
                            '<',
                            $end->toDateString()
                        )
                        ->where(
                            'departure_date',
                            '>',
                            $start->toDateString()
                        )
                        ->with('guest'),
            ])
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();
    }

    public function getGuestsProperty()
    {
        return Guest::query()
            ->orderBy('full_name')
            ->get([
                'id',
                'full_name',
                'email',
                'phone',
            ]);
    }

    public function getSelectedRoomProperty(): ?Room
    {
        if ($this->selectedRoomId === null) {
            return null;
        }

        return Room::query()
            ->with('roomType')
            ->find($this->selectedRoomId);
    }

    /*
    |--------------------------------------------------------------------------
    | Reservation Modal
    |--------------------------------------------------------------------------
    */

    public function openReservationModal(
        int $roomId,
        string $date,
    ): void {
        $room = Room::query()
            ->with('roomType')
            ->findOrFail($roomId);

        $arrivalDate = CarbonImmutable::parse($date);

        $this->selectedRoomId = $room->id;

        $this->selectedDate = $arrivalDate->toDateString();

        $this->departureDate = $arrivalDate
            ->addDay()
            ->toDateString();

        $this->nightlyRate = (string) $room
            ->roomType
            ->base_rate;

        $this->guestId = null;

        $this->adultCount = 1;

        $this->childCount = 0;

        $this->source = 'walk_in';

        $this->notes = null;

        $this->showReservationModal = true;
    }

    public function closeReservationModal(): void
    {
        $this->resetReservationForm();
    }

    public function createReservation(): void
    {
        $validated = $this->validate([
            'selectedRoomId' => [
                'required',
                'integer',
                'exists:rooms,id',
            ],

            'selectedDate' => [
                'required',
                'date',
            ],

            'departureDate' => [
                'required',
                'date',
                'after:selectedDate',
            ],

            'guestId' => [
                'required',
                'integer',
                'exists:guests,id',
            ],

            'adultCount' => [
                'required',
                'integer',
                'min:1',
            ],

            'childCount' => [
                'required',
                'integer',
                'min:0',
            ],

            'nightlyRate' => [
                'required',
                'numeric',
                'min:0',
            ],

            'source' => [
                'required',
                'string',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $room = Room::query()
            ->with('roomType')
            ->findOrFail($validated['selectedRoomId']);

        try {
            $reservation = app(CreateReservation::class)
                ->execute(
                    new CreateReservationData(
                        guestId: (int) $validated['guestId'],

                        roomTypeId: $room->room_type_id,

                        arrivalDate: CarbonImmutable::parse(
                            $validated['selectedDate']
                        ),

                        departureDate: CarbonImmutable::parse(
                            $validated['departureDate']
                        ),

                        adultCount: (int) $validated['adultCount'],

                        childCount: (int) $validated['childCount'],

                        nightlyRate: (string) $validated['nightlyRate'],

                        roomId: $room->id,

                        source: $validated['source'],

                        notes: $validated['notes'] ?? null,
                    )
                );
        } catch (
            DomainException |
            InvalidArgumentException $exception
        ) {
            Notification::make()
                ->title('Unable to create reservation')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Reservation created')
            ->body(
                sprintf(
                    '%s has been reserved for room %s.',
                    $reservation->guest->full_name,
                    $room->room_number,
                )
            )
            ->success()
            ->send();

        $this->resetReservationForm();
    }

    private function resetReservationForm(): void
    {
        $this->showReservationModal = false;

        $this->selectedRoomId = null;

        $this->selectedDate = null;

        $this->departureDate = null;

        $this->guestId = null;

        $this->adultCount = 1;

        $this->childCount = 0;

        $this->nightlyRate = null;

        $this->source = 'walk_in';

        $this->notes = null;

        $this->resetValidation();
    }

    public function reservationPosition($reservation): array
    {
        $chartStart = CarbonImmutable::parse($this->startDate);

        $chartEnd = $chartStart->addDays($this->days);

        $reservationStart = CarbonImmutable::parse(
            $reservation->arrival_date
        );

        $reservationEnd = CarbonImmutable::parse(
            $reservation->departure_date
        );

        $visibleStart = $reservationStart->greaterThan($chartStart)
            ? $reservationStart
            : $chartStart;

        $visibleEnd = $reservationEnd->lessThan($chartEnd)
            ? $reservationEnd
            : $chartEnd;

        return [
            'start' => $chartStart->diffInDays($visibleStart),
            'span' => max(
                1,
                $visibleStart->diffInDays($visibleEnd)
            ),
        ];
    }

    public function openReservationDetail(int $reservationId): void
    {
        $this->selectedReservationId = $reservationId;

        $this->showReservationDetailModal = true;
    }

    public function closeReservationDetail(): void
    {
        $this->selectedReservationId = null;

        $this->showReservationDetailModal = false;
    }

    public function getSelectedReservationProperty()
    {
        if ($this->selectedReservationId === null) {
            return null;
        }

        return \Modules\FrontOffice\Models\Reservation::query()
            ->with([
                'guest',
                'room',
                'roomType',
            ])
            ->find($this->selectedReservationId);
    }
}
