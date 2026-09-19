<?php

namespace Modules\FrontOffice\Filament\Resources\Guests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class GuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Guest')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('email')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('identity_type')
                    ->label('ID Type')
                    ->formatStateUsing(
                        fn (?string $state): string =>
                            match ($state) {
                                'national_id' => 'National ID',
                                'passport' => 'Passport',
                                'driving_license' => 'Driving License',
                                'other' => 'Other',
                                default => '-',
                            }
                    ),

                TextColumn::make('identity_number')
                    ->label('ID Number')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('nationality')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('reservations_count')
                    ->label('Reservations')
                    ->counts('reservations'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('full_name')
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