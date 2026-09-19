<?php

namespace Modules\FrontOffice\Filament\Resources\RoomTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class RoomTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('capacity')
                    ->label('Capacity')
                    ->suffix(' pax')
                    ->sortable(),

                TextColumn::make('base_rate')
                    ->label('Base Rate')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('rooms_count')
                    ->label('Rooms')
                    ->counts('rooms'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
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