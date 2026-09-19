<?php

namespace Modules\FrontOffice\Filament\Pages;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use InvalidArgumentException;
use Modules\FrontOffice\Actions\Reservations\ConfirmReservation;
use Modules\FrontOffice\Actions\Reservations\CancelReservation;
use Modules\FrontOffice\Actions\Reservations\CreateReservation;
use Modules\FrontOffice\Actions\Reservations\MarkReservationNoShow;
use Modules\FrontOffice\Actions\Reservations\UpdateReservation;
use Modules\FrontOffice\Actions\Stays\CheckInGuest;
use Modules\FrontOffice\Actions\Stays\CheckOutGuest;
use Modules\FrontOffice\Data\CreateReservationData;
use Modules\FrontOffice\Data\UpdateReservationData;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Models\Guest;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\RoomType;

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

    public ?int $roomTypeFilter = null;

    public ?int $floorFilter = null;

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

    public bool $showEditReservationModal = false;

    public ?int $editGuestId = null;

    public ?int $editRoomTypeId = null;

    public ?int $editRoomId = null;

    public ?string $editArrivalDate = null;

    public ?string $editDepartureDate = null;

    public int $editAdultCount = 1;

    public int $editChildCount = 0;

    public ?string $editNightlyRate = null;

    public ?string $editSource = null;

    public ?string $editNotes = null;

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

    public function clearFilters(): void
    {
        $this->roomTypeFilter = null;
        $this->floorFilter = null;
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
            ->when(
                $this->roomTypeFilter !== null,
                fn ($query) => $query->where('room_type_id', $this->roomTypeFilter),
            )
            ->when(
                $this->floorFilter !== null,
                fn ($query) => $query->where('floor', $this->floorFilter),
            )
            ->with([
                'roomType',

                'reservations' => fn ($query) =>
                    $query
                        ->whereIn('status', [
                            ReservationStatus::PENDING->value,
                            ReservationStatus::CONFIRMED->value,
                            ReservationStatus::CHECKED_IN->value,
                            ReservationStatus::CHECKED_OUT->value,
                            ReservationStatus::CANCELLED->value,
                            ReservationStatus::NO_SHOW->value,
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

    public function getRoomTypesProperty()
    {
        return RoomType::query()->orderBy('name')->get(['id', 'name']);
    }

    public function getFloorsProperty(): array
    {
        return Room::query()
            ->whereNotNull('floor')
            ->distinct()
            ->orderBy('floor')
            ->pluck('floor')
            ->all();
    }

    public function isDateBlocked(Room $room, CarbonInterface $date): bool
    {
        return $room->reservations->contains(
            fn (Reservation $reservation): bool => in_array($reservation->status, [
                ReservationStatus::PENDING,
                ReservationStatus::CONFIRMED,
                ReservationStatus::CHECKED_IN,
            ], true)
                && $date->greaterThanOrEqualTo($reservation->arrival_date)
                && $date->lessThan($reservation->departure_date)
        );
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

    public function getEditRoomsProperty()
    {
        if ($this->editRoomTypeId === null) {
            return collect();
        }

        return Room::query()
            ->where('room_type_id', $this->editRoomTypeId)
            ->where('status', '!=', RoomStatus::MAINTENANCE->value)
            ->orderBy('room_number')
            ->get(['id', 'room_number']);
    }

    public function updatedEditRoomTypeId(): void
    {
        if ($this->editRoomId !== null && ! Room::query()
            ->whereKey($this->editRoomId)
            ->where('room_type_id', $this->editRoomTypeId)
            ->exists()) {
            $this->editRoomId = null;
        }

        if ($this->editRoomTypeId !== null) {
            $this->editNightlyRate = (string) RoomType::query()
                ->whereKey($this->editRoomTypeId)
                ->value('base_rate');
        }
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

    public function reservationLayout(Room $room): array
    {
        $laneEnds = [];
        $positions = [];

        foreach ($room->reservations->sortBy('arrival_date') as $reservation) {
            $lane = 0;

            while (isset($laneEnds[$lane]) && $laneEnds[$lane]->greaterThan($reservation->arrival_date)) {
                $lane++;
            }

            $laneEnds[$lane] = $reservation->departure_date;
            $positions[$reservation->id] = [
                ...$this->reservationPosition($reservation),
                'lane' => $lane,
            ];
        }

        return [
            'positions' => $positions,
            'height' => max(80, count($laneEnds) * 72 + 8),
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

    public function getSelectedReservationProperty(): ?Reservation
    {
        if ($this->selectedReservationId === null) {
            return null;
        }

        return Reservation::query()
            ->with([
                'guest',
                'room',
                'roomType',
                'stay',
            ])
            ->find($this->selectedReservationId);
    }

    public function openEditReservationModal(): void
    {
        $reservation = $this->selectedReservation;

        if ($reservation === null || ! in_array($reservation->status, [
            ReservationStatus::PENDING,
            ReservationStatus::CONFIRMED,
        ], true)) {
            return;
        }

        $this->editGuestId = $reservation->guest_id;
        $this->editRoomTypeId = $reservation->room_type_id;
        $this->editRoomId = $reservation->room_id;
        $this->editArrivalDate = $reservation->arrival_date->toDateString();
        $this->editDepartureDate = $reservation->departure_date->toDateString();
        $this->editAdultCount = $reservation->adult_count;
        $this->editChildCount = $reservation->child_count;
        $this->editNightlyRate = $reservation->nightly_rate;
        $this->editSource = $reservation->source;
        $this->editNotes = $reservation->notes;
        $this->showReservationDetailModal = false;
        $this->showEditReservationModal = true;
        $this->resetValidation();
    }

    public function closeEditReservationModal(): void
    {
        $this->showEditReservationModal = false;
        $this->showReservationDetailModal = $this->selectedReservationId !== null;
        $this->resetValidation();
    }

    public function updateSelectedReservation(): void
    {
        $reservation = $this->selectedReservation;

        if ($reservation === null) {
            return;
        }

        $validated = $this->validate([
            'editGuestId' => ['required', 'integer', 'exists:guests,id'],
            'editRoomTypeId' => ['required', 'integer', 'exists:room_types,id'],
            'editRoomId' => ['nullable', 'integer', 'exists:rooms,id'],
            'editArrivalDate' => ['required', 'date'],
            'editDepartureDate' => ['required', 'date', 'after:editArrivalDate'],
            'editAdultCount' => ['required', 'integer', 'min:1'],
            'editChildCount' => ['required', 'integer', 'min:0'],
            'editNightlyRate' => ['required', 'numeric', 'min:0'],
            'editSource' => ['nullable', 'string'],
            'editNotes' => ['nullable', 'string'],
        ]);

        try {
            app(UpdateReservation::class)->execute(
                $reservation,
                new UpdateReservationData(
                    guestId: (int) $validated['editGuestId'],
                    roomTypeId: (int) $validated['editRoomTypeId'],
                    roomId: isset($validated['editRoomId']) ? (int) $validated['editRoomId'] : null,
                    arrivalDate: CarbonImmutable::parse($validated['editArrivalDate']),
                    departureDate: CarbonImmutable::parse($validated['editDepartureDate']),
                    adultCount: (int) $validated['editAdultCount'],
                    childCount: (int) $validated['editChildCount'],
                    nightlyRate: (string) $validated['editNightlyRate'],
                    source: $validated['editSource'] ?? null,
                    notes: $validated['editNotes'] ?? null,
                ),
            );
        } catch (DomainException | InvalidArgumentException $exception) {
            Notification::make()
                ->title('Unable to update reservation')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Reservation updated')
            ->success()
            ->send();

        $this->closeEditReservationModal();
    }

    public function confirmSelectedReservation(): void
    {
        $reservation = $this->selectedReservation;

        if ($reservation === null) {
            return;
        }

        try {
            app(ConfirmReservation::class)->execute($reservation);

            Notification::make()
                ->title('Reservation confirmed')
                ->success()
                ->send();
        } catch (DomainException | InvalidArgumentException $exception) {
            Notification::make()
                ->title('Unable to confirm reservation')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function checkInSelectedReservation(): void
    {
        $reservation = $this->selectedReservation;

        if ($reservation === null) {
            return;
        }

        try {
            app(CheckInGuest::class)->execute($reservation);

            Notification::make()
                ->title('Guest checked in')
                ->success()
                ->send();
        } catch (DomainException | InvalidArgumentException $exception) {
            Notification::make()
                ->title('Unable to check in guest')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function checkOutSelectedReservation(): void
    {
        $reservation = $this->selectedReservation;

        if ($reservation === null) {
            return;
        }

        if ($reservation->stay === null) {
            Notification::make()
                ->title('Unable to check out guest')
                ->body('No stay exists for this reservation.')
                ->danger()
                ->send();

            return;
        }

        try {
            app(CheckOutGuest::class)->execute($reservation->stay);

            Notification::make()
                ->title('Guest checked out')
                ->success()
                ->send();
        } catch (DomainException | InvalidArgumentException $exception) {
            Notification::make()
                ->title('Unable to check out guest')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelSelectedReservation(): void
    {
        $reservation = $this->selectedReservation;

        if ($reservation === null) {
            return;
        }

        try {
            app(CancelReservation::class)->execute($reservation);

            Notification::make()
                ->title('Reservation cancelled')
                ->success()
                ->send();
        } catch (DomainException | InvalidArgumentException $exception) {
            Notification::make()
                ->title('Unable to cancel reservation')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function markSelectedReservationNoShow(): void
    {
        $reservation = $this->selectedReservation;

        if ($reservation === null) {
            return;
        }

        try {
            app(MarkReservationNoShow::class)->execute($reservation);

            Notification::make()
                ->title('Reservation marked no-show')
                ->success()
                ->send();
        } catch (DomainException | InvalidArgumentException $exception) {
            Notification::make()
                ->title('Unable to mark no-show')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
}
