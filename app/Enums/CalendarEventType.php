<?php

namespace App\Enums;

enum CalendarEventType: string
{
    case Single = 'single';
    case Repeat = 'repeat';
    case Range = 'range';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function toString(): ?string
    {
        return match ($this) {
            self::Single => 'Одноразовое',
            self::Repeat => 'Повторяющееся',
            self::Range => 'Многодневное',
        };
    }
}
