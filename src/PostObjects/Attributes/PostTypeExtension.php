<?php

namespace VAF\WP\Framework\PostObjects\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class PostTypeExtension
{
    public function __construct(public readonly array $postTypes = [])
    {
    }
}
