<?php

namespace VAF\WP\Framework\Template\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsTemplate
{
    public function __construct(public readonly string $templateFile)
    {
    }
}
