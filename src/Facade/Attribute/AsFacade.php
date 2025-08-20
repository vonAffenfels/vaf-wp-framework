<?php

namespace VAF\WP\Framework\Facade\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsFacade
{
    public function __construct(
        public readonly string $facadeAccessor
    ) {
    }
}
