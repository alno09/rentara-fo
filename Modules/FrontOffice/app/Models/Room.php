<?php

namespace Modules\FrontOffice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\FrontOffice\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FrontOffice\Database\Factories\RoomFactory;

class Room extends Model
{
    use HasFactory;

    protected static function newFactory(): RoomFactory
    {
        return RoomFactory::new();
    }
    
    protected $fillable = [
        'room_type_id',
        'room_number',
        'floor',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'status' => RoomStatus::class,
        ];
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function stays(): HasMany
    {
        return $this->hasMany(Stay::class);
    }
}