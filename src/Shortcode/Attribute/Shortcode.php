<?php

namespace VAF\WP\Library\Shortcode\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Shortcode
{
    public function __construct(public readonly string $tag)
    {
    }
}
