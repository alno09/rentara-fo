<x-filament-panels::page>
    @php
        $today = today()->toDateString();
    @endphp
    <div class="space-y-4">

        {{-- Toolbar --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex shrink-0 items-center gap-2">
                <x-filament::button
                    color="gray"
                    wire:click="previousPeriod"
                >
                    Previous
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    wire:click="goToToday"
                >
                    Today
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    wire:click="nextPeriod"
                >
                    Next
                </x-filament::button>
            </div>

            <div class="min-w-36 text-sm font-medium text-gray-600 dark:text-gray-300">
                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                —
                {{
                    \Carbon\Carbon::parse($startDate)
                        ->addDays($days - 1)
                        ->format('d M Y')
                }}
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:ml-auto">
                <label for="room-type-filter" class="sr-only">Room Type</label>
                <select
                    id="room-type-filter"
                    wire:model.live="roomTypeFilter"
                    class="min-w-40 rounded-md border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900"
                >
                    <option value="">All Room Types</option>
                    @foreach ($this->roomTypes as $roomType)
                        <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                    @endforeach
                </select>

                <label for="floor-filter" class="sr-only">Floor</label>
                <select
                    id="floor-filter"
                    wire:model.live="floorFilter"
                    class="min-w-28 rounded-md border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900"
                >
                    <option value="">All Floors</option>
                    @foreach ($this->floors as $floor)
                        <option value="{{ $floor }}">Floor {{ $floor }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-600 dark:text-gray-300" aria-label="Status legend">
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-amber-400"></span>Pending</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-400"></span>Confirmed</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-sky-400"></span>Checked In</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-gray-400"></span>Checked Out</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-gray-200 ring-1 ring-gray-300"></span>Cancelled</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-rose-400"></span>No Show</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span>Dirty room</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>Maintenance</span>
        </div>

        {{-- Room Chart --}}
        <div class="overflow-auto rounded-md border border-gray-200 dark:border-gray-800" style="max-height: 72vh;">
            <div
                class="min-w-max"
                style="
                    display: grid;
                    grid-template-columns:
                        180px
                        repeat({{ $days }}, minmax(120px, 1fr));
                "
            >
                {{-- Header: Room column --}}
                <div
                    class="
                        sticky top-0 left-0 z-40
                        border-b
                        border-r
                        border-gray-200
                        bg-gray-50
                        p-3
                        font-semibold
                        dark:border-gray-800
                        dark:bg-gray-900
                    "
                >
                    Room
                </div>

                {{-- Header: Dates --}}
                @foreach ($this->dates as $date)
                    <div
                        class="
                            sticky top-0 z-30
                            border-b
                            border-r
                            border-gray-200
                            p-3
                            text-center
                            dark:border-gray-800
                            {{ $date->toDateString() === $today
                                ? 'bg-cyan-100 dark:bg-cyan-950'
                                : 'bg-gray-50 dark:bg-gray-900' }}
                        "
                    >
                        <div class="font-semibold">
                            {{ $date->format('D') }}
                        </div>

                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $date->format('d M') }}
                        </div>
                    </div>
                @endforeach

                {{-- Room rows --}}
                @foreach ($this->rooms as $room)
                    @php
                        $layout = $this->reservationLayout($room);
                    @endphp
                    {{-- Room info --}}
                    <div
                        class="
                            sticky left-0 z-20
                            border-b
                            border-r
                            border-gray-200
                            bg-white
                            p-3
                            dark:border-gray-800
                            dark:bg-gray-950
                        "
                    >
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $this->roomStatusStyle($room->status) }}" aria-hidden="true"></span>
                            <span>{{ $room->room_number }}</span>
                        </div>

                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $room->roomType->name }} · {{ ucfirst($room->status->value) }}
                        </div>

                        <div class="mt-1 text-xs text-gray-400">
                            Floor {{ $room->floor ?? '-' }}
                        </div>
                    </div>

                    {{-- Timeline area --}}
                    <div
                        class="relative border-b border-gray-200 dark:border-gray-800"
                        style="
                            grid-column: span {{ $days }};
                            display: grid;
                            min-height: {{ $layout['height'] }}px;
                            grid-template-columns:
                                repeat({{ $days }}, minmax(120px, 1fr));
                        "
                    >
                        {{-- Empty clickable cells --}}
                        @foreach ($this->dates as $date)
                            @php
                                $blocked = $this->isDateBlocked($room, $date);
                            @endphp

                            <button
                                type="button"
                                aria-label="{{ $blocked ? 'Reserved' : 'Reserve room '.$room->room_number.' on '.$date->format('d M Y') }}"
                                @if (! $blocked)
                                    wire:click="
                                        openReservationModal(
                                            {{ $room->id }},
                                            '{{ $date->toDateString() }}'
                                        )
                                    "
                                @endif
                                class="
                                    min-h-20
                                    border-r
                                    border-gray-200
                                    transition-colors
                                    dark:border-gray-800
                                    {{ $date->toDateString() === $today
                                        ? 'bg-cyan-50/60 dark:bg-cyan-950/30'
                                        : '' }}

                                    @if (! $blocked)
                                        group hover:bg-cyan-100 focus-visible:bg-cyan-100
                                        dark:hover:bg-cyan-900/40 dark:focus-visible:bg-cyan-900/40
                                    @endif
                                "
                                @if ($blocked) disabled @endif
                            >
                                @if (! $blocked)
                                    <span class="text-sm text-cyan-600 opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100 dark:text-cyan-300" aria-hidden="true">
                                        +
                                    </span>
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
                                wire:click="
                                    openReservationDetail(
                                        {{ $reservation->id }}
                                    )
                                "
                                class="
                                    absolute
                                    z-10
                                    overflow-hidden rounded-md
                                    border
                                    px-3
                                    py-2
                                    text-left
                                    text-xs
                                    shadow-sm
                                    hover:shadow-md
                                    {{ $this->reservationStyle($reservation->status) }}
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
                                <div class="truncate font-semibold">
                                    {{ $reservation->guest->full_name }}
                                </div>

                                <div class="mt-1 truncate font-medium">
                                    {{
                                        ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $reservation->status->value
                                            )
                                        )
                                    }}
                                </div>
                                @if ($span > 1)
                                    <div class="mt-0.5 truncate opacity-75">
                                        {{ $reservation->reservation_number }}
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endforeach
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
                <div
                    class="
                        w-full
                        max-w-2xl
                        overflow-hidden
                        rounded-2xl
                        bg-white
                        shadow-2xl
                        dark:bg-gray-900
                    "
                >
                    {{-- Header --}}
                    <div
                        class="
                            border-b
                            border-gray-200
                            px-6
                            py-5
                            dark:border-gray-800
                        "
                    >
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
                                    <p
                                        class="
                                            mt-1
                                            text-sm
                                            text-gray-500
                                            dark:text-gray-400
                                        "
                                    >
                                        Room
                                        {{ $this->selectedRoom->room_number }}

                                        ·

                                        {{ $this->selectedRoom->roomType->name }}
                                    </p>
                                @endif
                            </div>

                            <button
                                type="button"
                                wire:click="closeReservationModal"
                                class="
                                    rounded-lg
                                    p-2
                                    text-gray-500
                                    hover:bg-gray-100
                                    dark:hover:bg-gray-800
                                "
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    {{-- Form --}}
                    <form
                        wire:submit="createReservation"
                        class="space-y-6 p-6"
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
                        <div
                            class="
                                grid
                                grid-cols-1
                                gap-4
                                md:grid-cols-2
                            "
                        >
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
                        <div
                            class="
                                grid
                                grid-cols-1
                                gap-4
                                md:grid-cols-2
                            "
                        >
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
                        <div
                            class="
                                grid
                                grid-cols-1
                                gap-4
                                md:grid-cols-2
                            "
                        >
                            <div>
                                <label
                                    class="
                                        mb-2
                                        block
                                        text-sm
                                        font-medium
                                    "
                                >
                                    Nightly Rate
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
                        <div
                            class="
                                flex
                                justify-end
                                gap-3
                                border-t
                                border-gray-200
                                pt-5
                                dark:border-gray-800
                            "
                        >
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
                <div
                    class="
                        w-full
                        max-w-lg
                        rounded-2xl
                        bg-white
                        p-6
                        shadow-2xl
                        dark:bg-gray-900
                    "
                >
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
                                {{ $reservation->guest->full_name }}
                            </h2>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    text-gray-500
                                    dark:text-gray-400
                                "
                            >
                                {{ $reservation->reservation_number }}
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="closeReservationDetail"
                            class="
                                rounded-lg
                                p-2
                                text-gray-500
                                hover:bg-gray-100
                                dark:hover:bg-gray-800
                            "
                        >
                            ✕
                        </button>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div
                            class="
                                grid
                                grid-cols-2
                                gap-4
                                text-sm
                            "
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
                                    Status
                                </div>

                                <span class="mt-1 inline-flex rounded-sm border px-2 py-0.5 text-xs font-semibold {{ $this->reservationStyle($reservation->status) }}">
                                    {{ ucwords(str_replace('_', ' ', $reservation->status->value)) }}
                                </span>
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
                    </div>

                    <div
                        class="
                            mt-6
                            flex
                            flex-wrap
                            items-center
                            justify-between
                            gap-3
                            border-t
                            border-gray-200
                            pt-4
                            dark:border-gray-800
                        "
                    >
                        <div class="order-2 flex flex-wrap gap-2 sm:order-1">
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
        @endif

    </div>
</x-filament-panels::page>
