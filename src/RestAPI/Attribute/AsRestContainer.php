<?php

namespace VAF\WP\Framework\RestAPI\Attribute;

use Attribute;

/**
 * Service tag to autoconfigure rest API container.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AsRestContainer
{
    public function __construct(
        public readonly string $namespace = ''
    ) {
    }
}
