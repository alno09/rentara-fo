<?php

namespace Modules\FrontOffice\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\FrontOffice\Enums\RoomStatus;

final class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Room Information')
                    ->schema([
                        TextInput::make('room_number')
                            ->label('Room Number')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        Select::make('room_type_id')
                            ->label('Room Type')
                            ->relationship('roomType', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('floor')
                            ->numeric()
                            ->minValue(0),

                        Select::make('status')
                            ->options([
                                RoomStatus::AVAILABLE->value => 'Available',
                                RoomStatus::OCCUPIED->value => 'Occupied',
                                RoomStatus::DIRTY->value => 'Dirty',
                                RoomStatus::MAINTENANCE->value => 'Maintenance',
                            ])
                            ->default(RoomStatus::AVAILABLE->value)
                            ->required(),

                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}