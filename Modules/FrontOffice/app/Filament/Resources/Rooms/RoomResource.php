<?php

namespace Modules\FrontOffice\Filament\Resources\Rooms;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\FrontOffice\Filament\Resources\Rooms\Pages\CreateRoom;
use Modules\FrontOffice\Filament\Resources\Rooms\Pages\EditRoom;
use Modules\FrontOffice\Filament\Resources\Rooms\Pages\ListRooms;
use Modules\FrontOffice\Filament\Resources\Rooms\Schemas\RoomForm;
use Modules\FrontOffice\Filament\Resources\Rooms\Tables\RoomsTable;
use Modules\FrontOffice\Models\Room;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationLabel = 'Rooms';

    protected static ?string $modelLabel = 'Room';

    protected static ?string $pluralModelLabel = 'Rooms';

    protected static string | \UnitEnum | null $navigationGroup = 'Front Office';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return RoomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRooms::route('/'),
            'create' => CreateRoom::route('/create'),
            'edit' => EditRoom::route('/{record}/edit'),
        ];
    }
}