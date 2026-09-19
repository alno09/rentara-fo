<?php

namespace Modules\FrontOffice\Filament\Resources\Guests;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\FrontOffice\Filament\Resources\Guests\Pages\CreateGuest;
use Modules\FrontOffice\Filament\Resources\Guests\Pages\EditGuest;
use Modules\FrontOffice\Filament\Resources\Guests\Pages\ListGuests;
use Modules\FrontOffice\Filament\Resources\Guests\Schemas\GuestForm;
use Modules\FrontOffice\Filament\Resources\Guests\Tables\GuestsTable;
use Modules\FrontOffice\Models\Guest;

class GuestResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static ?string $navigationLabel = 'Guests';

    protected static ?string $modelLabel = 'Guest';

    protected static ?string $pluralModelLabel = 'Guests';

    protected static string | \UnitEnum | null $navigationGroup = 'Front Office';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return GuestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuests::route('/'),
            'create' => CreateGuest::route('/create'),
            'edit' => EditGuest::route('/{record}/edit'),
        ];
    }
}