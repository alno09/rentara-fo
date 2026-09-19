<x-filament-panels::page>
    <div class="space-y-4">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
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

            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                —
                {{
                    \Carbon\Carbon::parse($startDate)
                        ->addDays($days - 1)
                        ->format('d M Y')
                }}
            </div>
        </div>

        {{-- Room Chart --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
            <div
                class="min-w-[1000px]"
                style="
                    display: grid;
                    grid-template-columns:
                        160px
                        repeat({{ $days }}, minmax(120px, 1fr));
                "
            >
                {{-- Header: Room column --}}
                <div
                    class="
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
                            border-b
                            border-r
                            border-gray-200
                            bg-gray-50
                            p-3
                            text-center
                            dark:border-gray-800
                            dark:bg-gray-900
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
                    {{-- Room info --}}
                    <div
                        class="
                            border-b
                            border-r
                            border-gray-200
                            bg-white
                            p-3
                            dark:border-gray-800
                            dark:bg-gray-950
                        "
                    >
                        <div class="font-semibold">
                            {{ $room->room_number }}
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $room->roomType->name }}
                        </div>

                        <div class="mt-1 text-xs text-gray-400">
                            Floor {{ $room->floor ?? '-' }}
                        </div>
                    </div>

                    {{-- Date cells --}}
                    @foreach ($this->dates as $date)
                        @php
                            $reservation = $room
                                ->reservations
                                ->first(function ($reservation) use ($date) {
                                    return $date->gte(
                                        $reservation->arrival_date
                                    ) && $date->lt(
                                        $reservation->departure_date
                                    );
                                });
                        @endphp

                        <button
                            type="button"
                            @if (! $reservation)
                                wire:click="
                                    openReservationModal(
                                        {{ $room->id }},
                                        '{{ $date->toDateString() }}'
                                    )
                                "
                            @endif
                            class="
                                min-h-20
                                border-b
                                border-r
                                border-gray-200
                                p-2
                                text-left
                                transition
                                dark:border-gray-800

                                @if (! $reservation)
                                    hover:bg-gray-50
                                    dark:hover:bg-white/5
                                @else
                                    cursor-default
                                @endif
                            "
                        >
                            @if ($reservation)
                                <div
                                    class="
                                        rounded-lg
                                        border
                                        border-gray-300
                                        bg-gray-50
                                        p-2
                                        text-xs
                                        dark:border-gray-700
                                        dark:bg-gray-900
                                    "
                                >
                                    <div class="font-semibold">
                                        {{ $reservation->guest->full_name }}
                                    </div>

                                    <div class="mt-1 text-gray-500 dark:text-gray-400">
                                        {{ $reservation->reservation_number }}
                                    </div>

                                    <div class="mt-1">
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
                                </div>
                            @else
                                <div
                                    class="
                                        flex
                                        h-full
                                        min-h-16
                                        items-center
                                        justify-center
                                        text-xs
                                        text-gray-300
                                        dark:text-gray-700
                                    "
                                >
                                    +
                                </div>
                            @endif
                        </button>
                    @endforeach
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

    </div>
</x-filament-panels::page>