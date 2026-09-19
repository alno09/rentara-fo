<?php

namespace Modules\FrontOffice\Filament\Resources\Rooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\FrontOffice\Enums\RoomStatus;

final class RoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room_number')
                    ->label('Room')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roomType.name')
                    ->label('Room Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('floor')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(
                        fn (RoomStatus|string $state): string =>
                            $state instanceof RoomStatus
                                ? match ($state) {
                                    RoomStatus::AVAILABLE => 'Available',
                                    RoomStatus::OCCUPIED => 'Occupied',
                                    RoomStatus::DIRTY => 'Dirty',
                                    RoomStatus::MAINTENANCE => 'Maintenance',
                                }
                                : ucfirst($state)
                    )
                    ->sortable(),

                TextColumn::make('roomType.base_rate')
                    ->label('Base Rate')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        RoomStatus::AVAILABLE->value => 'Available',
                        RoomStatus::OCCUPIED->value => 'Occupied',
                        RoomStatus::DIRTY->value => 'Dirty',
                        RoomStatus::MAINTENANCE->value => 'Maintenance',
                    ]),

                SelectFilter::make('room_type_id')
                    ->label('Room Type')
                    ->relationship('roomType', 'name'),
            ])
            ->defaultSort('room_number')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}