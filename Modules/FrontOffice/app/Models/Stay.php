<?php

namespace Modules\FrontOffice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FrontOffice\Enums\StayStatus;

class Stay extends Model
{
    protected $fillable = [
        'reservation_id',
        'guest_id',
        'room_id',
        'checked_in_at',
        'expected_check_out_at',
        'checked_out_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'expected_check_out_at' => 'datetime',
            'checked_out_at' => 'datetime',

            'status' => StayStatus::class,
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}