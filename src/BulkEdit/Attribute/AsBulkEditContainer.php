<?php

namespace VAF\WP\Framework\BulkEdit\Attribute;

use Attribute;

/**
 * Service tag to autoconfigure bulk edit container.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AsBulkEditContainer
{
}
