<?php

namespace Modules\FrontOffice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'identity_type',
        'identity_number',
        'nationality',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function stays(): HasMany
    {
        return $this->hasMany(Stay::class);
    }
}