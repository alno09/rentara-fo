<?php

namespace Modules\FrontOffice\Filament\Resources\Reservations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\FrontOffice\Enums\RoomStatus;
use Modules\FrontOffice\Models\Room;
use Modules\FrontOffice\Models\RoomType;

final class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Guest & Room')
                    ->schema([
                        Select::make('guest_id')
                            ->label('Guest')
                            ->relationship(
                                name: 'guest',
                                titleAttribute: 'full_name'
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('room_type_id')
                            ->label('Room Type')
                            ->relationship(
                                name: 'roomType',
                                titleAttribute: 'name'
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, $set): void {
                                $set('room_id', null);

                                if (! $state) {
                                    $set('nightly_rate', null);

                                    return;
                                }

                                $rate = RoomType::query()
                                    ->whereKey($state)
                                    ->value('base_rate');

                                $set('nightly_rate', $rate);
                            })
                            ->required(),

                        Select::make('room_id')
                            ->label('Room')
                            ->options(function ($get): array {
                                $roomTypeId = $get('room_type_id');

                                if (! $roomTypeId) {
                                    return [];
                                }

                                return Room::query()
                                    ->where('room_type_id', $roomTypeId)
                                    ->whereNot(
                                        'status',
                                        RoomStatus::MAINTENANCE->value
                                    )
                                    ->orderBy('room_number')
                                    ->pluck('room_number', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->nullable()
                            ->helperText(
                                'Optional. Room can also be assigned later.'
                            ),
                    ])
                    ->columns(2),

                Section::make('Stay Details')
                    ->schema([
                        DatePicker::make('arrival_date')
                            ->label('Arrival')
                            ->native(false)
                            ->required(),

                        DatePicker::make('departure_date')
                            ->label('Departure')
                            ->native(false)
                            ->after('arrival_date')
                            ->required(),

                        TextInput::make('adult_count')
                            ->label('Adults')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),

                        TextInput::make('child_count')
                            ->label('Children')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Rate & Source')
                    ->schema([
                        TextInput::make('nightly_rate')
                            ->label('Nightly Rate')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->required(),

                        Select::make('source')
                            ->options([
                                'walk_in' => 'Walk In',
                                'phone' => 'Phone',
                                'website' => 'Website',
                                'ota' => 'OTA',
                                'corporate' => 'Corporate',
                                'other' => 'Other',
                            ])
                            ->default('walk_in'),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
