<?php

namespace Modules\FrontOffice\Filament\Resources\Stays;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Modules\FrontOffice\Filament\Resources\Stays\Pages\ListStays;
use Modules\FrontOffice\Filament\Resources\Stays\Tables\StaysTable;
use Modules\FrontOffice\Models\Stay;

class StayResource extends Resource
{
    protected static ?string $model = Stay::class;

    protected static ?string $navigationLabel = 'Stays';

    protected static ?string $modelLabel = 'Stay';

    protected static ?string $pluralModelLabel = 'Stays';

    protected static string | \UnitEnum | null $navigationGroup = 'Front Office';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return StaysTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStays::route('/'),
        ];
    }
}