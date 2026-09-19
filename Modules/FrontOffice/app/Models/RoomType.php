<?php

namespace Modules\FrontOffice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FrontOffice\Database\Factories\RoomTypeFactory;

class RoomType extends Model
{
    use HasFactory;

    protected static function newFactory(): RoomTypeFactory
    {
        return RoomTypeFactory::new();
    }
    
    protected $fillable = [
        'name',
        'code',
        'description',
        'capacity',
        'base_rate',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'base_rate' => 'decimal:2',
        ];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}