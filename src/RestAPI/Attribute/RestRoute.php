<?php

namespace VAF\WP\Framework\RestAPI\Attribute;

use Attribute;
use VAF\WP\Framework\RestAPI\RestRouteMethod;

#[Attribute(Attribute::TARGET_METHOD)]
class RestRoute
{
    public function __construct(
        public readonly string $uri,
        public readonly RestRouteMethod $method = RestRouteMethod::GET,
        public readonly ?string $requiredPermission = null,
        public readonly bool $wrapResponse = true,
        public readonly bool $suppressEchoOutput = true,
    ) {
    }
}
