<?php

namespace VAF\WP\Framework\Shortcode\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Shortcode
{
    public function __construct(public readonly string $tag)
    {
    }
}
