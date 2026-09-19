<?php

namespace Modules\FrontOffice\Enums;

enum RoomStatus: string
{
    case AVAILABLE = 'available';
    case OCCUPIED = 'occupied';
    case DIRTY = 'dirty';
    case MAINTENANCE = 'maintenance';
}