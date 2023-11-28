<?php

namespace VAF\WP\Framework\Setting;

use Closure;

class Conversion
{

    public function __construct(
        public readonly Closure $fromDb,
        public readonly Closure $toDb,
        public readonly ?Closure $fromInput = null,
    )
    {
    }

    public static function boolean(): self
    {
        return new self(
            fromDb: fn($value) => in_array($value, ['true', '1', 'active']),
            toDb: fn($value) => $value ? 'true' : 'false',
            fromInput: fn($value) => is_bool($value) ? $value : in_array($value, ['true', '1', 'active']),
        );
    }
}
