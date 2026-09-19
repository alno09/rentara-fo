<?php

namespace Modules\FrontOffice\Filament\Resources\RoomTypes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class RoomTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Room Type Information')
                    ->description(
                        'Define room category, capacity, and base rate.'
                    )
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),

                        TextInput::make('capacity')
                            ->numeric()
                            ->minValue(1)
                            ->default(2)
                            ->required(),

                        TextInput::make('base_rate')
                            ->label('Base Rate')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->required(),

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}