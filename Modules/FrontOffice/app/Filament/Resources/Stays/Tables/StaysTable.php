<?php

namespace Modules\FrontOffice\Filament\Resources\Stays\Tables;

use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\FrontOffice\Actions\Stays\CheckOutGuest;
use Modules\FrontOffice\Enums\StayStatus;
use Modules\FrontOffice\Models\Stay;

final class StaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('guest.full_name')
                    ->label('Guest')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('room.room_number')
                    ->label('Room')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reservation.reservation_number')
                    ->label('Reservation')
                    ->searchable(),

                TextColumn::make('checked_in_at')
                    ->label('Check In')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('expected_check_out_at')
                    ->label('Expected Check Out')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('checked_out_at')
                    ->label('Actual Check Out')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof StayStatus
                                ? match ($state) {
                                    StayStatus::ACTIVE => 'Active',
                                    StayStatus::COMPLETED => 'Completed',
                                }
                                : ucfirst((string) $state)
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        StayStatus::ACTIVE->value => 'Active',
                        StayStatus::COMPLETED->value => 'Completed',
                    ]),
            ])
            ->recordActions([
                self::checkOutAction(),
            ])
            ->defaultSort('checked_in_at', 'desc');
    }

    private static function checkOutAction(): Action
    {
        return Action::make('check_out')
            ->label('Check Out')
            ->requiresConfirmation()
            ->modalHeading('Check out guest')
            ->modalDescription(
                'The stay will be completed and the room will be marked as dirty.'
            )
            ->visible(
                fn (Stay $record): bool =>
                    $record->status === StayStatus::ACTIVE
            )
            ->action(function (Stay $record): void {
                try {
                    app(CheckOutGuest::class)
                        ->execute($record);

                    Notification::make()
                        ->title('Guest checked out')
                        ->body(
                            sprintf(
                                '%s has been checked out from room %s.',
                                $record->guest->full_name,
                                $record->room->room_number,
                            )
                        )
                        ->success()
                        ->send();
                } catch (DomainException $exception) {
                    Notification::make()
                        ->title('Unable to check out guest')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}