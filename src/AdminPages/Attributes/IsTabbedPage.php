<?php

namespace VAF\WP\Framework\AdminPages\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class IsTabbedPage
{
    public function __construct(
        public readonly string $pageTitle,
        public readonly ?string $defaultSlug = null,
        public readonly string $pageVar = 'page'
    ) {
    }
}
