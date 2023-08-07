<?php

namespace VAF\WP\Framework\PostObjects\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Field
{
    public function __construct(public readonly string $fieldName)
    {
    }
}
