<?php

namespace VAF\WP\Framework\GutenbergBlock\Attribute;

use Attribute;

/**
 * Service tag to autoconfigure hook container.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class GutenbergBlock
{
    public function __construct(
        public readonly string $type,
        public readonly string $editorScript,
        public readonly int $apiVersion = 2,
    )
    {
    }
}
