<x-filament-panels::page>
    <style>
        .rc-shell { --rc-surface: #fff; --rc-subtle: #f7f9f9; --rc-line: #dce3e4; --rc-text: #17272b; --rc-muted: #53656a; --rc-today: #e9f5f4; --rc-hover: #e7f3f1; color: var(--rc-text); }
        .dark .rc-shell { --rc-surface: #172226; --rc-subtle: #202d31; --rc-line: #354449; --rc-text: #f1f5f4; --rc-muted: #b5c3c5; --rc-today: #183d3d; --rc-hover: #214946; }
        .rc-toolbar { display: flex; flex-wrap: wrap; align-items: end; gap: .75rem 1rem; }
        .rc-range { min-width: 9rem; font-size: .875rem; font-weight: 600; line-height: 1.25; }
        .rc-range small { display: block; color: var(--rc-muted); font-size: .7rem; font-weight: 500; text-transform: uppercase; }
        .rc-filters { display: flex; flex-wrap: wrap; align-items: end; gap: .5rem; margin-left: auto; }
        .rc-filter label { display: block; margin-bottom: .2rem; color: var(--rc-muted); font-size: .7rem; font-weight: 600; }
        .rc-filter select { min-width: 8rem; height: 2.25rem; padding: 0 .75rem; border: 1px solid var(--rc-line); border-radius: .3rem; background: var(--rc-surface); color: var(--rc-text); font-size: .8rem; }
        .rc-legend { display: flex; flex-wrap: wrap; gap: .35rem 1rem; color: var(--rc-muted); font-size: .72rem; }
        .rc-legend span { display: inline-flex; align-items: center; gap: .35rem; white-space: nowrap; }
        .rc-legend i { display: inline-block; width: .55rem; height: .55rem; border-radius: .15rem; background: var(--status-accent); }
        .rc-viewport { max-height: 72vh; overflow: auto; border: 1px solid var(--rc-line); border-radius: .35rem; background: var(--rc-surface); }
        .rc-grid { display: grid; width: 100%; }
        .rc-corner, .rc-date { position: sticky; top: 0; z-index: 30; min-height: 3.25rem; padding: .55rem .7rem; border-right: 1px solid var(--rc-line); border-bottom: 1px solid var(--rc-line); background: var(--rc-subtle); }
        .rc-corner { left: 0; z-index: 40; display: flex; align-items: center; font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--rc-muted); }
        .rc-date { text-align: center; line-height: 1.2; }
        .rc-date strong { display: block; font-size: .75rem; }
        .rc-date small { color: var(--rc-muted); font-size: .7rem; }
        .rc-date[data-today="true"] { background: var(--rc-today); box-shadow: inset 0 3px #0d9488; }
        .rc-room { position: sticky; left: 0; z-index: 20; min-height: 5rem; padding: .7rem; border-right: 1px solid var(--rc-line); border-bottom: 1px solid var(--rc-line); background: var(--rc-surface); }
        .rc-room strong { font-size: .95rem; }
        .rc-room small { display: block; color: var(--rc-muted); font-size: .72rem; }
        .rc-room-status { display: inline-flex; align-items: center; gap: .3rem; color: var(--rc-muted); font-size: .68rem; }
        .rc-room-status::before { content: ''; width: .5rem; height: .5rem; border-radius: 50%; background: var(--room-accent); }
        .rc-timeline { position: relative; display: grid; border-bottom: 1px solid var(--rc-line); }
        .rc-cell { min-height: 5rem; border-right: 1px solid var(--rc-line); background: transparent; text-align: center; }
        .rc-cell[data-today="true"] { background: var(--rc-today); }
        .rc-cell:not(:disabled) { cursor: pointer; }
        .rc-cell:not(:disabled):hover, .rc-cell:not(:disabled):focus-visible { background: var(--rc-hover); outline: 2px solid #0d9488; outline-offset: -2px; }
        .rc-cell span { color: #0f766e; font-size: .8rem; font-weight: 600; opacity: 0; }
        .rc-cell:hover span, .rc-cell:focus-visible span { opacity: 1; }
        .rc-bar { position: absolute; z-index: 10; overflow: hidden; padding: .42rem .55rem; border: 1px solid var(--status-accent); border-left-width: 4px; border-radius: .25rem; background: var(--status-surface); color: var(--status-text); text-align: left; cursor: pointer; box-shadow: 0 1px 2px #00000014; transition: box-shadow .15s, transform .15s; }
        .rc-bar:hover, .rc-bar:focus-visible { box-shadow: 0 3px 9px #00000025; transform: translateY(-1px); outline: 2px solid var(--status-accent); outline-offset: 1px; }
        .rc-bar strong, .rc-bar small { display: block; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .rc-bar strong { font-size: .74rem; }
        .rc-bar small { font-size: .68rem; }
        .rc-bar:is([data-status="cancelled"], [data-status="no_show"]) { opacity: .78; }
        .rc-status { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .5rem; border: 1px solid var(--status-accent); border-radius: .25rem; background: var(--status-surface); color: var(--status-text); font-size: .7rem; font-weight: 700; white-space: nowrap; }
        .rc-shell [data-status="pending"] { --status-accent: #b7791f; --status-surface: #fff7e6; --status-text: #704307; }
        .rc-shell [data-status="confirmed"] { --status-accent: #15835b; --status-surface: #eaf8f0; --status-text: #145c43; }
        .rc-shell [data-status="checked_in"] { --status-accent: #177ca5; --status-surface: #e8f5fa; --status-text: #145875; }
        .rc-shell [data-status="checked_out"] { --status-accent: #71818a; --status-surface: #f0f3f4; --status-text: #42545c; }
        .rc-shell [data-status="cancelled"] { --status-accent: #8c969b; --status-surface: #f4f5f5; --status-text: #58676e; }
        .rc-shell [data-status="no_show"] { --status-accent: #bb5a70; --status-surface: #fbeef1; --status-text: #7b3447; }
        .dark .rc-shell [data-status="pending"] { --status-surface: #453518; --status-text: #ffe6aa; }
        .dark .rc-shell [data-status="confirmed"] { --status-surface: #163f34; --status-text: #b5f3d0; }
        .dark .rc-shell [data-status="checked_in"] { --status-surface: #193d50; --status-text: #bde9f8; }
        .dark .rc-shell [data-status="checked_out"] { --status-surface: #303d43; --status-text: #d8e2e5; }
        .dark .rc-shell [data-status="cancelled"] { --status-surface: #2b3438; --status-text: #c5d0d3; }
        .dark .rc-shell [data-status="no_show"] { --status-surface: #4d2935; --status-text: #fbd1da; }
        .rc-shell [data-room-status="available"] { --room-accent: #159267; }
        .rc-shell [data-room-status="occupied"] { --room-accent: #1888ad; }
        .rc-shell [data-room-status="dirty"] { --room-accent: #d0782d; }
        .rc-shell [data-room-status="maintenance"] { --room-accent: #ca5362; }
        .rc-legend i[data-room-status] { background: var(--room-accent); border-radius: 50%; }
        .rc-empty { grid-column: 1 / -1; padding: 2rem; text-align: center; color: var(--rc-muted); }
        .rc-empty strong { display: block; margin-bottom: .25rem; color: var(--rc-text); font-size: .9rem; }
        .rc-dialog { width: min(100%, 40rem); max-height: 90vh; overflow-y: auto; border-radius: .4rem; background: var(--rc-surface); color: var(--rc-text); box-shadow: 0 16px 48px #0004; }
        .rc-dialog-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--rc-line); background: var(--rc-subtle); }
        .rc-dialog-body { padding: 1.25rem; }
        .rc-dialog-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .65rem; padding-top: 1rem; border-top: 1px solid var(--rc-line); }
        .rc-dialog label { display: block; margin-bottom: .3rem; font-size: .75rem; font-weight: 600; color: var(--rc-muted); }
        .rc-dialog :is(select, input, textarea) { width: 100%; border: 1px solid var(--rc-line); border-radius: .3rem; background: var(--rc-surface); color: var(--rc-text); }
        .rc-dialog :is(select, input) { min-height: 2.4rem; }
        .rc-dialog :is(select, input, textarea):focus { border-color: #0d9488; outline: 2px solid #0d948866; }
        .rc-dialog .rc-close { min-width: 2rem; min-height: 2rem; border-radius: .25rem; color: var(--rc-muted); font-size: 1.3rem; }
        .rc-dialog .rc-close:hover { background: var(--rc-hover); color: var(--rc-text); }
        .rc-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        .rc-modal-form { display: grid; gap: 1rem; }
        .rc-detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem 1rem; font-size: .82rem; }
        .rc-detail-grid dt { color: var(--rc-muted); font-size: .72rem; }
        .rc-detail-grid dd { margin-top: .2rem; font-weight: 600; overflow-wrap: anywhere; }
        .rc-detail-grid > div > div:first-child { color: var(--rc-muted); font-size: .72rem; }
        .rc-detail-grid > div > div:last-child { margin-top: .2rem; font-weight: 600; overflow-wrap: anywhere; }
        @media (max-width: 640px) { .rc-filters { width: 100%; margin-left: 0; } .rc-filter { flex: 1 1 8rem; } .rc-filter select { width: 100%; } .rc-dialog-body { padding: 1rem; } .rc-form-grid { grid-template-columns: 1fr; } }
    </style>
    @php
        $today = today()->toDateString();
        $rooms = $this->rooms;
        $hasReservations = $rooms->contains(fn ($room) => $room->reservations->isNotEmpty());
    @endphp
    <div class="rc-shell space-y-4">

        {{-- Toolbar --}}
        <div class="rc-toolbar">
            <div class="flex shrink-0 items-center gap-2">
                <x-filament::button
                    color="gray"
                    icon="heroicon-m-chevron-left"
                    wire:click="previousPeriod"
                    tooltip="Previous period"
                >
                    Previous
                </x-filament::button>

                <x-filament::button
                    color="primary"
                    wire:click="goToToday"
                >
                    Today
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    icon="heroicon-m-chevron-right"
                    icon-position="after"
                    wire:click="nextPeriod"
                    tooltip="Next period"
                >
                    Next
                </x-filament::button>
            </div>

            <div class="rc-range">
                <small>Visible dates</small>
                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                —
                {{
                    \Carbon\Carbon::parse($startDate)
                        ->addDays($days - 1)
                        ->format('d M Y')
                }}
            </div>

            <div class="rc-filters">
                <div class="rc-filter">
                <label for="room-type-filter">Room Type</label>
                <select
                    id="room-type-filter"
                    wire:model.live="roomTypeFilter"
                >
                    <option value="">All Room Types</option>
                    @foreach ($this->roomTypes as $roomType)
                        <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                    @endforeach
                </select>
                </div>

                <div class="rc-filter">
                <label for="floor-filter">Floor</label>
                <select
                    id="floor-filter"
                    wire:model.live="floorFilter"
                >
                    <option value="">All Floors</option>
                    @foreach ($this->floors as $floor)
                        <option value="{{ $floor }}">Floor {{ $floor }}</option>
                    @endforeach
                </select>
                </div>
            </div>
        </div>

        <div class="rc-legend" aria-label="Status legend">
            <span><i data-status="pending" aria-hidden="true"></i>Pending</span>
            <span><i data-status="confirmed" aria-hidden="true"></i>Confirmed</span>
            <span><i data-status="checked_in" aria-hidden="true"></i>Checked In</span>
            <span><i data-status="checked_out" aria-hidden="true"></i>Checked Out</span>
            <span><i data-status="cancelled" aria-hidden="true"></i>Cancelled</span>
            <span><i data-status="no_show" aria-hidden="true"></i>No Show</span>
            <span><i data-room-status="dirty" aria-hidden="true"></i>Dirty</span>
            <span><i data-room-status="maintenance" aria-hidden="true"></i>Maintenance</span>
        </div>

        @if ($rooms->isNotEmpty() && ! $hasReservations)
            <p class="text-xs" style="color: var(--rc-muted);">No reservations in this period.</p>
        @endif

        {{-- Room Chart --}}
        <div class="rc-viewport" data-testid="room-chart-viewport">
            <div
                class="rc-grid"
                style="
                    min-width: {{ 164 + $days * 118 }}px;
                    grid-template-columns:
                        164px
                        repeat({{ $days }}, minmax(118px, 1fr));
                "
            >
                {{-- Header: Room column --}}
                <div class="rc-corner">
                    Room
                </div>

                {{-- Header: Dates --}}
                @foreach ($this->dates as $date)
                    <div class="rc-date" data-today="{{ $date->toDateString() === $today ? 'true' : 'false' }}">
                        <strong>
                            {{ $date->format('D') }}
                        </strong>

                        <small>
                            {{ $date->format('d M') }}
                        </small>
                    </div>
                @endforeach

                {{-- Room rows --}}
                @forelse ($rooms as $room)
                    @php
                        $layout = $this->reservationLayout($room);
                    @endphp
                    {{-- Room info --}}
                    <div class="rc-room">
                        <strong>{{ $room->room_number }}</strong>
                        <small>{{ $room->roomType->name }} · Floor {{ $room->floor ?? '-' }}</small>
                        <span class="rc-room-status" data-room-status="{{ $room->status->value }}">{{ ucfirst($room->status->value) }}</span>
                    </div>

                    {{-- Timeline area --}}
                    <div
                        class="rc-timeline"
                        style="
                            grid-column: span {{ $days }};
                            min-height: {{ $layout['height'] }}px;
                            grid-template-columns:
                                repeat({{ $days }}, minmax(118px, 1fr));
                        "
                    >
                        {{-- Empty clickable cells --}}
                        @foreach ($this->dates as $date)
                            @php
                                $blocked = $this->isDateBlocked($room, $date);
                            @endphp

                            <button
                                type="button"
                                class="rc-cell"
                                data-today="{{ $date->toDateString() === $today ? 'true' : 'false' }}"
                                aria-label="{{ $blocked ? 'Reserved' : 'Reserve room '.$room->room_number.' on '.$date->format('d M Y') }}"
                                @if (! $blocked)
                                    wire:click="
                                        openReservationModal(
                                            {{ $room->id }},
                                            '{{ $date->toDateString() }}'
                                        )
                                    "
                                @endif
                                @if ($blocked) disabled @endif
                            >
                                @if (! $blocked)
                                    <span aria-hidden="true">+ Book</span>
                                @endif
                            </button>
                        @endforeach

                        {{-- Reservation bars --}}
                        @foreach ($room->reservations as $reservation)
                            @php
                                $position = $layout['positions'][$reservation->id];

                                $span =
                                    $position['span'];
                            @endphp

                            <button
                                type="button"
                                class="rc-bar"
                                data-status="{{ $reservation->status->value }}"
                                title="{{ $reservation->guest->full_name }} · {{ $reservation->reservation_number }}"
                                wire:click="
                                    openReservationDetail(
                                        {{ $reservation->id }}
                                    )
                                "
                                style="
                                    top: {{ 8 + $position['lane'] * 72 }}px;
                                    left:
                                        calc(
                                            (100% / {{ $days }})
                                            * {{ $position['start'] }}
                                        );

                                    width:
                                        calc(
                                            (100% / {{ $days }})
                                            * {{ $span }} - 8px
                                        );

                                    margin-left: 4px;
                                "
                            >
                                <strong>
                                    {{ $reservation->guest->full_name }}
                                </strong>

                                <small>
                                    {{
                                        ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $reservation->status->value
                                            )
                                        )
                                    }}
                                </small>
                                @if ($span > 1)
                                    <small style="opacity: .76;">
                                        {{ $reservation->reservation_number }}
                                    </small>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @empty
                    <div class="rc-empty">
                        @if ($roomTypeFilter !== null || $floorFilter !== null)
                            <strong>No rooms match these filters</strong>
                            <x-filament::button color="gray" wire:click="clearFilters">Clear filters</x-filament::button>
                        @else
                            <strong>No rooms yet</strong>
                            <span>Rooms will appear here once added.</span>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Reservation Modal --}}
        @if ($showReservationModal)
            <div
                class="
                    fixed
                    inset-0
                    z-50
                    flex
                    items-center
                    justify-center
                    bg-black/50
                    p-4
                "
            >
                <div class="rc-dialog">
                    {{-- Header --}}
                    <div class="rc-dialog-header">
                        <div
                            class="
                                flex
                                items-start
                                justify-between
                                gap-4
                            "
                        >
                            <div>
                                <h2 class="text-lg font-semibold">
                                    New Reservation
                                </h2>

                                @if ($this->selectedRoom)
                                    <p class="mt-1 text-sm" style="color: var(--rc-muted);">
                                        Room {{ $this->selectedRoom->room_number }} · {{ $this->selectedRoom->roomType->name }}
                                    </p>
                                @endif
                            </div>

                            <button
                                type="button"
                                wire:click="closeReservationModal"
                                aria-label="Close new reservation"
                                class="rc-close"
                            >
                                &times;
                            </button>
                        </div>
                    </div>

                    {{-- Form --}}
                    <form
                        wire:submit="createReservation"
                        class="rc-dialog-body rc-modal-form"
                    >
                        {{-- Guest --}}
                        <div>
                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                "
                            >
                                Guest
                            </label>

                            <select
                                wire:model="guestId"
                                class="
                                    w-full
                                    rounded-lg
                                    border-gray-300
                                    bg-white
                                    dark:border-gray-700
                                    dark:bg-gray-900
                                "
                            >
                                <option value="">
                                    Select guest
                                </option>

                                @foreach ($this->guests as $guest)
                                    <option value="{{ $guest->id }}">
                                        {{ $guest->full_name }}

                                        @if ($guest->phone)
                                            — {{ $guest->phone }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            @error('guestId')
                                <p class="mt-1 text-sm text-danger-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Dates --}}
                        <div class="rc-form-grid">
                            <div>
                                <label
                                    class="
                                        mb-2
                                        block
                                        text-sm
                                        font-medium
                                    "
                                >
                                    Arrival
                                </label>

                                <input
                                    type="date"
                                    wire:model="selectedDate"
                                    class="
                                        w-full
                                        rounded-lg
                                        border-gray-300
                                        dark:border-gray-700
                                        dark:bg-gray-900
                                    "
                                >

                                @error('selectedDate')
                                    <p class="mt-1 text-sm text-danger-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="
                                        mb-2
                                        block
                                        text-sm
                                        font-medium
                                    "
                                >
                                    Departure
                                </label>

                                <input
                                    type="date"
                                    wire:model="departureDate"
                                    class="
                                        w-full
                                        rounded-lg
                                        border-gray-300
                                        dark:border-gray-700
                                        dark:bg-gray-900
                                    "
                                >

                                @error('departureDate')
                                    <p class="mt-1 text-sm text-danger-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Guests --}}
                        <div class="rc-form-grid">
                            <div>
                                <label
                                    class="
                                        mb-2
                                        block
                                        text-sm
                                        font-medium
                                    "
                                >
                                    Adults
                                </label>

                                <input
                                    type="number"
                                    min="1"
                                    wire:model="adultCount"
                                    class="
                                        w-full
                                        rounded-lg
                                        border-gray-300
                                        dark:border-gray-700
                                        dark:bg-gray-900
                                    "
                                >
                            </div>

                            <div>
                                <label
                                    class="
                                        mb-2
                                        block
                                        text-sm
                                        font-medium
                                    "
                                >
                                    Children
                                </label>

                                <input
                                    type="number"
                                    min="0"
                                    wire:model="childCount"
                                    class="
                                        w-full
                                        rounded-lg
                                        border-gray-300
                                        dark:border-gray-700
                                        dark:bg-gray-900
                                    "
                                >
                            </div>
                        </div>

                        {{-- Rate & source --}}
                        <div class="rc-form-grid">
                            <div>
                                <label
                                    class="
                                        mb-2
                                        block
                                        text-sm
                                        font-medium
                                    "
                                >
                                    Nightly Rate (Rp)
                                </label>

                                <input
                                    type="number"
                                    min="0"
                                    wire:model="nightlyRate"
                                    class="
                                        w-full
                                        rounded-lg
                                        border-gray-300
                                        dark:border-gray-700
                                        dark:bg-gray-900
                                    "
                                >

                                @error('nightlyRate')
                                    <p class="mt-1 text-sm text-danger-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="
                                        mb-2
                                        block
                                        text-sm
                                        font-medium
                                    "
                                >
                                    Source
                                </label>

                                <select
                                    wire:model="source"
                                    class="
                                        w-full
                                        rounded-lg
                                        border-gray-300
                                        bg-white
                                        dark:border-gray-700
                                        dark:bg-gray-900
                                    "
                                >
                                    <option value="walk_in">
                                        Walk In
                                    </option>

                                    <option value="phone">
                                        Phone
                                    </option>

                                    <option value="website">
                                        Website
                                    </option>

                                    <option value="ota">
                                        OTA
                                    </option>

                                    <option value="corporate">
                                        Corporate
                                    </option>

                                    <option value="other">
                                        Other
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                "
                            >
                                Notes
                            </label>

                            <textarea
                                wire:model="notes"
                                rows="3"
                                class="
                                    w-full
                                    rounded-lg
                                    border-gray-300
                                    dark:border-gray-700
                                    dark:bg-gray-900
                                "
                            ></textarea>
                        </div>

                        {{-- Actions --}}
                        <div class="rc-dialog-footer" style="justify-content: flex-end;">
                            <x-filament::button
                                type="button"
                                color="gray"
                                wire:click="closeReservationModal"
                            >
                                Cancel
                            </x-filament::button>

                            <x-filament::button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="createReservation"
                            >
                                <span
                                    wire:loading.remove
                                    wire:target="createReservation"
                                >
                                    Create Reservation
                                </span>

                                <span
                                    wire:loading
                                    wire:target="createReservation"
                                >
                                    Creating...
                                </span>
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if (
            $showReservationDetailModal &&
            $this->selectedReservation
        )
            @php
                $reservation = $this->selectedReservation;
            @endphp

            <div
                class="
                    fixed
                    inset-0
                    z-50
                    flex
                    items-center
                    justify-center
                    bg-black/50
                    p-4
                "
            >
                <div class="rc-dialog">
                    <div
                        class="rc-dialog-header
                            flex
                            items-start
                            justify-between
                            gap-4
                        "
                    >
                        <div>
                            <h2 class="text-lg font-semibold">
                                {{ $reservation->guest->full_name }}
                            </h2>

                            <p
                                class="mt-1 text-sm"
                                style="color: var(--rc-muted);"
                            >
                                {{ $reservation->reservation_number }}
                            </p>
                            <span class="rc-status mt-2" data-status="{{ $reservation->status->value }}">
                                {{ ucwords(str_replace('_', ' ', $reservation->status->value)) }}
                            </span>
                        </div>

                        <button
                            type="button"
                            wire:click="closeReservationDetail"
                            class="rc-close"
                            aria-label="Close reservation detail"
                        >
                            &times;
                        </button>
                    </div>

                    <div class="rc-dialog-body space-y-5">
                        <div
                            class="rc-detail-grid"
                        >
                            <div>
                                <div class="text-gray-500">
                                    Room
                                </div>

                                <div class="font-medium">
                                    {{
                                        $reservation->room?->room_number
                                        ?? 'Unassigned'
                                    }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">
                                    Room Type
                                </div>

                                <div class="font-medium">
                                    {{ $reservation->roomType->name }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">
                                    Arrival
                                </div>

                                <div class="font-medium">
                                    {{
                                        $reservation
                                            ->arrival_date
                                            ->format('d M Y')
                                    }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">
                                    Departure
                                </div>

                                <div class="font-medium">
                                    {{
                                        $reservation
                                            ->departure_date
                                            ->format('d M Y')
                                    }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">
                                    Nightly Rate
                                </div>

                                <div class="font-medium">
                                    Rp
                                    {{
                                        number_format(
                                            $reservation->nightly_rate,
                                            0,
                                            ',',
                                            '.'
                                        )
                                    }}
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-500">Nights</div>
                                <div class="font-medium">{{ (int) $reservation->arrival_date->diffInDays($reservation->departure_date) }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Guests</div>
                                <div class="font-medium">{{ $reservation->adult_count }} adults · {{ $reservation->child_count }} children</div>
                            </div>
                            @if ($reservation->source)
                                <div>
                                    <div class="text-gray-500">Source</div>
                                    <div class="font-medium">{{ ucwords(str_replace('_', ' ', $reservation->source)) }}</div>
                                </div>
                            @endif
                        </div>

                        @if ($reservation->notes)
                            <div>
                                <div
                                    class="
                                        text-sm
                                        text-gray-500
                                    "
                                >
                                    Notes
                                </div>

                                <div class="mt-1 text-sm">
                                    {{ $reservation->notes }}
                                </div>
                            </div>
                        @endif
                        <div class="rc-dialog-footer">
                        <div class="order-2 flex flex-wrap gap-2 sm:order-1">
                            @if (in_array($reservation->status, [\Modules\FrontOffice\Enums\ReservationStatus::PENDING, \Modules\FrontOffice\Enums\ReservationStatus::CONFIRMED], true))
                                <x-filament::button
                                    color="gray"
                                    wire:click="openEditReservationModal"
                                >
                                    Edit Reservation
                                </x-filament::button>
                            @endif

                            @if ($reservation->status === \Modules\FrontOffice\Enums\ReservationStatus::PENDING)
                                <x-filament::button
                                    wire:click="confirmSelectedReservation"
                                    wire:loading.attr="disabled"
                                    wire:target="confirmSelectedReservation"
                                >
                                    <span wire:loading.remove wire:target="confirmSelectedReservation">Confirm Reservation</span>
                                    <span wire:loading wire:target="confirmSelectedReservation">Confirming...</span>
                                </x-filament::button>
                            @elseif ($reservation->status === \Modules\FrontOffice\Enums\ReservationStatus::CONFIRMED)
                                <x-filament::button
                                    wire:click="checkInSelectedReservation"
                                    wire:loading.attr="disabled"
                                    wire:target="checkInSelectedReservation"
                                >
                                    <span wire:loading.remove wire:target="checkInSelectedReservation">Check In</span>
                                    <span wire:loading wire:target="checkInSelectedReservation">Checking In...</span>
                                </x-filament::button>
                            @elseif ($reservation->status === \Modules\FrontOffice\Enums\ReservationStatus::CHECKED_IN)
                                <x-filament::button
                                    wire:click="checkOutSelectedReservation"
                                    wire:loading.attr="disabled"
                                    wire:target="checkOutSelectedReservation"
                                >
                                    <span wire:loading.remove wire:target="checkOutSelectedReservation">Check Out</span>
                                    <span wire:loading wire:target="checkOutSelectedReservation">Checking Out...</span>
                                </x-filament::button>
                            @endif

                            @if (in_array($reservation->status, [\Modules\FrontOffice\Enums\ReservationStatus::PENDING, \Modules\FrontOffice\Enums\ReservationStatus::CONFIRMED], true))
                                <x-filament::button
                                    color="danger"
                                    outlined
                                    wire:click="cancelSelectedReservation"
                                    wire:confirm="Cancel this reservation? The room inventory for these dates will become available again."
                                    wire:loading.attr="disabled"
                                    wire:target="cancelSelectedReservation"
                                >
                                    <span wire:loading.remove wire:target="cancelSelectedReservation">Cancel Reservation</span>
                                    <span wire:loading wire:target="cancelSelectedReservation">Cancelling...</span>
                                </x-filament::button>
                            @endif

                            @if ($reservation->status === \Modules\FrontOffice\Enums\ReservationStatus::CONFIRMED)
                                <x-filament::button
                                    color="warning"
                                    outlined
                                    wire:click="markSelectedReservationNoShow"
                                    wire:confirm="Mark this reservation as no-show? No stay will be created."
                                    wire:loading.attr="disabled"
                                    wire:target="markSelectedReservationNoShow"
                                >
                                    <span wire:loading.remove wire:target="markSelectedReservationNoShow">Mark No Show</span>
                                    <span wire:loading wire:target="markSelectedReservationNoShow">Marking No Show...</span>
                                </x-filament::button>
                            @endif
                        </div>

                        <x-filament::button
                            class="order-1 sm:order-2"
                            color="gray"
                            wire:click="closeReservationDetail"
                        >
                            Close
                        </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($showEditReservationModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="rc-dialog" style="width: min(100%, 42rem);">
                    <div class="rc-dialog-header flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Edit Reservation</h2>
                        <button type="button" wire:click="closeEditReservationModal" aria-label="Close edit reservation" class="rc-close">&times;</button>
                    </div>

                    <form wire:submit="updateSelectedReservation" class="rc-dialog-body space-y-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="edit-guest" class="mb-1 block text-sm font-medium">Guest</label>
                                <select id="edit-guest" wire:model="editGuestId" class="w-full rounded-md border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900">
                                    <option value="">Select guest</option>
                                    @foreach ($this->guests as $guest)
                                        <option value="{{ $guest->id }}">{{ $guest->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('editGuestId') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="edit-room-type" class="mb-1 block text-sm font-medium">Room Type</label>
                                <select id="edit-room-type" wire:model.live="editRoomTypeId" class="w-full rounded-md border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900">
                                    <option value="">Select room type</option>
                                    @foreach ($this->roomTypes as $roomType)
                                        <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                                    @endforeach
                                </select>
                                @error('editRoomTypeId') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="edit-room" class="mb-1 block text-sm font-medium">Room</label>
                                <select id="edit-room" wire:model="editRoomId" class="w-full rounded-md border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900">
                                    <option value="">Unassigned</option>
                                    @foreach ($this->editRooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                                    @endforeach
                                </select>
                                @error('editRoomId') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="edit-rate" class="mb-1 block text-sm font-medium">Nightly Rate (Rp)</label>
                                <input id="edit-rate" type="number" min="0" step="0.01" wire:model="editNightlyRate" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                @error('editNightlyRate') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="edit-arrival" class="mb-1 block text-sm font-medium">Arrival</label>
                                <input id="edit-arrival" type="date" wire:model="editArrivalDate" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                @error('editArrivalDate') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="edit-departure" class="mb-1 block text-sm font-medium">Departure</label>
                                <input id="edit-departure" type="date" wire:model="editDepartureDate" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                @error('editDepartureDate') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="edit-adults" class="mb-1 block text-sm font-medium">Adults</label>
                                <input id="edit-adults" type="number" min="1" wire:model="editAdultCount" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                @error('editAdultCount') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="edit-children" class="mb-1 block text-sm font-medium">Children</label>
                                <input id="edit-children" type="number" min="0" wire:model="editChildCount" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                @error('editChildCount') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="edit-source" class="mb-1 block text-sm font-medium">Source</label>
                                <select id="edit-source" wire:model="editSource" class="w-full rounded-md border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900">
                                    <option value="">None</option>
                                    <option value="walk_in">Walk In</option>
                                    <option value="phone">Phone</option>
                                    <option value="website">Website</option>
                                    <option value="ota">OTA</option>
                                    <option value="corporate">Corporate</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('editSource') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="edit-notes" class="mb-1 block text-sm font-medium">Notes</label>
                            <textarea id="edit-notes" wire:model="editNotes" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900"></textarea>
                            @error('editNotes') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end gap-2 border-t border-gray-200 pt-4 dark:border-gray-800">
                            <x-filament::button type="button" color="gray" wire:click="closeEditReservationModal">Cancel</x-filament::button>
                            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="updateSelectedReservation">
                                <span wire:loading.remove wire:target="updateSelectedReservation">Save Changes</span>
                                <span wire:loading wire:target="updateSelectedReservation">Saving...</span>
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
