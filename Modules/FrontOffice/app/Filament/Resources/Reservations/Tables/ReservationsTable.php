<?php

namespace Modules\FrontOffice\Filament\Resources\Reservations\Tables;

use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use InvalidArgumentException;
use Modules\FrontOffice\Actions\Reservations\AssignRoom;
use Modules\FrontOffice\Actions\Reservations\ConfirmReservation;
use Modules\FrontOffice\Actions\Stays\CheckInGuest;
use Modules\FrontOffice\Enums\ReservationStatus;
use Modules\FrontOffice\Models\Reservation;
use Modules\FrontOffice\Models\Room;

final class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reservation_number')
                    ->label('Reservation')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guest.full_name')
                    ->label('Guest')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roomType.name')
                    ->label('Room Type'),

                TextColumn::make('room.room_number')
                    ->label('Room')
                    ->placeholder('Unassigned'),

                TextColumn::make('arrival_date')
                    ->label('Arrival')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('nightly_rate')
                    ->label('Rate')
                    ->money('IDR'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof ReservationStatus
                                ? match ($state) {
                                    ReservationStatus::PENDING =>
                                        'Pending',

                                    ReservationStatus::CONFIRMED =>
                                        'Confirmed',

                                    ReservationStatus::CHECKED_IN =>
                                        'Checked In',

                                    ReservationStatus::CHECKED_OUT =>
                                        'Checked Out',

                                    ReservationStatus::CANCELLED =>
                                        'Cancelled',

                                    ReservationStatus::NO_SHOW =>
                                        'No Show',
                                }
                                : ucfirst(
                                    str_replace('_', ' ', $state)
                                )
                    ),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->options([
                        ReservationStatus::PENDING->value =>
                            'Pending',

                        ReservationStatus::CONFIRMED->value =>
                            'Confirmed',

                        ReservationStatus::CHECKED_IN->value =>
                            'Checked In',

                        ReservationStatus::CHECKED_OUT->value =>
                            'Checked Out',

                        ReservationStatus::CANCELLED->value =>
                            'Cancelled',

                        ReservationStatus::NO_SHOW->value =>
                            'No Show',
                    ]),
            ])

            ->recordActions([
                self::confirmAction(),
                self::assignRoomAction(),
                self::checkInAction(),

            ])

            ->defaultSort('arrival_date');
    }

    private static function confirmAction(): Action
    {
        return Action::make('confirm')
            ->label('Confirm')
            ->requiresConfirmation()
            ->visible(
                fn (Reservation $record): bool =>
                    $record->status === ReservationStatus::PENDING
            )
            ->action(function (Reservation $record): void {
                try {
                    app(ConfirmReservation::class)
                        ->execute($record);

                    Notification::make()
                        ->title('Reservation confirmed')
                        ->success()
                        ->send();
                } catch (DomainException $exception) {
                    Notification::make()
                        ->title('Unable to confirm reservation')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    private static function assignRoomAction(): Action
    {
        return Action::make('assign_room')
            ->label('Assign Room')
            ->visible(
                fn (Reservation $record): bool =>
                    in_array(
                        $record->status,
                        [
                            ReservationStatus::PENDING,
                            ReservationStatus::CONFIRMED,
                        ],
                        true,
                    )
            )
            ->schema([
                \Filament\Forms\Components\Select::make('room_id')
                    ->label('Room')
                    ->options(
                        function (Reservation $record): array {
                            return Room::query()
                                ->where(
                                    'room_type_id',
                                    $record->room_type_id
                                )
                                ->orderBy('room_number')
                                ->pluck(
                                    'room_number',
                                    'id'
                                )
                                ->all();
                        }
                    )
                    ->searchable()
                    ->required(),
            ])
            ->action(
                function (
                    Reservation $record,
                    array $data
                ): void {
                    try {
                        $room = Room::query()
                            ->findOrFail($data['room_id']);

                        app(AssignRoom::class)
                            ->execute(
                                reservation: $record,
                                room: $room,
                            );

                        Notification::make()
                            ->title('Room assigned')
                            ->success()
                            ->send();
                    } catch (DomainException | InvalidArgumentException $exception) {
                        Notification::make()
                            ->title('Unable to assign room')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }
            );
    }

    private static function checkInAction(): Action
    {
        return Action::make('check_in')
            ->label('Check In')
            ->requiresConfirmation()
            ->visible(
                fn (Reservation $record): bool =>
                    $record->status ===
                        ReservationStatus::CONFIRMED
            )
            ->action(function (Reservation $record): void {
                try {
                    app(CheckInGuest::class)
                        ->execute($record);

                    Notification::make()
                        ->title('Guest checked in')
                        ->body(
                            sprintf(
                                '%s has been successfully checked in.',
                                $record->guest->full_name
                            )
                        )
                        ->success()
                        ->send();
                } catch (DomainException | InvalidArgumentException $exception) {
                    Notification::make()
                        ->title('Unable to check in guest')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
