<?php

namespace VAF\WP\Library\Hook\Attribute;

use Attribute;

/**
 * Service tag to autoconfigure hook container.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AsHookContainer
{
}
