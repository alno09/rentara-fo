<?php

namespace Modules\FrontOffice\Enums;

enum RoomStatus: string
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case OCCUPIED = 'occupied';
    case DIRTY = 'dirty';
    case MAINTENANCE = 'maintenance';
}