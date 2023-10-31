<?php

namespace VAF\WP\Framework\Setting;

use Closure;

class Migration
{
    public function __construct(
        public readonly Closure $migrated,
        public readonly Closure $value,
        public readonly Closure $clear,
    )
    {
    }
}
