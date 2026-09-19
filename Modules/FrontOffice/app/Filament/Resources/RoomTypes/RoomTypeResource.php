<?php

namespace Modules\FrontOffice\Filament\Resources\RoomTypes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\FrontOffice\Filament\Resources\RoomTypes\Pages\CreateRoomType;
use Modules\FrontOffice\Filament\Resources\RoomTypes\Pages\EditRoomType;
use Modules\FrontOffice\Filament\Resources\RoomTypes\Pages\ListRoomTypes;
use Modules\FrontOffice\Filament\Resources\RoomTypes\Schemas\RoomTypeForm;
use Modules\FrontOffice\Filament\Resources\RoomTypes\Tables\RoomTypesTable;
use Modules\FrontOffice\Models\RoomType;

class RoomTypeResource extends Resource
{
    protected static ?string $model = RoomType::class;

    protected static ?string $navigationLabel = 'Room Types';

    protected static ?string $modelLabel = 'Room Type';

    protected static ?string $pluralModelLabel = 'Room Types';

    protected static string | \UnitEnum | null $navigationGroup = 'Front Office';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return RoomTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoomTypes::route('/'),
            'create' => CreateRoomType::route('/create'),
            'edit' => EditRoomType::route('/{record}/edit'),
        ];
    }
}
