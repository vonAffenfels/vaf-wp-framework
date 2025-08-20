<?php

namespace VAF\WP\Framework\Hook\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class PreventAutowiring
{
    public function __construct()
    {
    }
}
