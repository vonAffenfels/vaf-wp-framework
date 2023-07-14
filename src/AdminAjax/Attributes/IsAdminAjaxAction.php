<?php

namespace VAF\WP\Framework\AdminAjax\Attributes;

use Attribute;
use VAF\WP\Framework\Utils\Capabilities;

#[Attribute(Attribute::TARGET_METHOD)]
class IsAdminAjaxAction
{
    public function __construct(
        public readonly string $action,
        public readonly ?Capabilities $capabilities = null
    ) {
    }
}
