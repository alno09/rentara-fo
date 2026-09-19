<?php

namespace Modules\FrontOffice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
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