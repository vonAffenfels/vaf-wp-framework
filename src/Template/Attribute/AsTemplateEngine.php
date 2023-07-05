<?php

namespace VAF\WP\Framework\Template\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsTemplateEngine
{
    public function __construct(public readonly string $extension)
    {
    }
}
