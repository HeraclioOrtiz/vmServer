<?php

namespace App\Enums;

enum PromotionStatus: string
{
    case NONE = 'none';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::NONE => 'Sin promoción',
            self::PENDING => 'Promoción pendiente',
            self::APPROVED => 'Promoción aprobada',
            self::REJECTED => 'Promoción rechazada',
        };
    }

    public function canPromote(): bool
    {
        return $this === self::NONE;
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }
}
