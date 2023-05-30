<?php

namespace VAF\WP\Library\RestAPI\Attribute;

use Attribute;
use VAF\WP\Library\RestAPI\RestRouteMethod;

#[Attribute(Attribute::TARGET_METHOD)]
class RestRoute
{
    public function __construct(
        public readonly string $uri,
        public readonly RestRouteMethod $method
    ) {
    }
}
