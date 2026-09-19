<?php

namespace Modules\FrontOffice\Filament\Resources\Reservations;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\FrontOffice\Filament\Resources\Reservations\Pages\CreateReservation;
use Modules\FrontOffice\Filament\Resources\Reservations\Pages\ListReservations;
use Modules\FrontOffice\Filament\Resources\Reservations\Schemas\ReservationForm;
use Modules\FrontOffice\Filament\Resources\Reservations\Tables\ReservationsTable;
use Modules\FrontOffice\Models\Reservation;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationLabel = 'Reservations';

    protected static ?string $modelLabel = 'Reservation';

    protected static ?string $pluralModelLabel = 'Reservations';

    protected static string | \UnitEnum | null $navigationGroup = 'Front Office';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ReservationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReservationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservations::route('/'),
            'create' => CreateReservation::route('/create'),
        ];
    }
}
