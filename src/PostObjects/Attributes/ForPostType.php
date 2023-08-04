<?php

namespace VAF\WP\Framework\PostObjects\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class ForPostType
{
    public function __construct(
        public readonly string $postType
    ) {
    }
}
