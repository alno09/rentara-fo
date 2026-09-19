<?php

namespace Modules\FrontOffice\Filament\Resources\Guests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class GuestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Guest Information')
                    ->schema([
                        TextInput::make('full_name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('nationality')
                            ->maxLength(100),
                    ])
                    ->columns(2),

                Section::make('Identity')
                    ->schema([
                        Select::make('identity_type')
                            ->label('Identity Type')
                            ->options([
                                'national_id' => 'National ID',
                                'passport' => 'Passport',
                                'driving_license' => 'Driving License',
                                'other' => 'Other',
                            ])
                            ->native(false),

                        TextInput::make('identity_number')
                            ->label('Identity Number')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Contact & Emergency')
                    ->schema([
                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('emergency_contact_name')
                            ->label('Emergency Contact Name')
                            ->maxLength(255),

                        TextInput::make('emergency_contact_phone')
                            ->label('Emergency Contact Phone')
                            ->tel()
                            ->maxLength(50),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}