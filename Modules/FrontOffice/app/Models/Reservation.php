<?php

namespace Modules\FrontOffice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\FrontOffice\Database\Factories\ReservationFactory;
use Modules\FrontOffice\Enums\ReservationStatus;

class Reservation extends Model
{
    use HasFactory;

    protected static function newFactory(): ReservationFactory
    {
        return ReservationFactory::new();
    }

    protected $fillable = [
        'reservation_number',
        'guest_id',
        'room_type_id',
        'room_id',
        'arrival_date',
        'departure_date',
        'adult_count',
        'child_count',
        'nightly_rate',
        'status',
        'source',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'arrival_date' => 'date',
            'departure_date' => 'date',

            'adult_count' => 'integer',
            'child_count' => 'integer',

            'nightly_rate' => 'decimal:2',

            'status' => ReservationStatus::class,
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function stay(): HasOne
    {
        return $this->hasOne(Stay::class);
    }
}
