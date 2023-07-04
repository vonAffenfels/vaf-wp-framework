<?php

namespace VAF\WP\Framework\Menu\Attribute;

use Attribute;
use VAF\WP\Framework\Utils\Capabilities;
use VAF\WP\Framework\Utils\Dashicons;

#[Attribute(Attribute::TARGET_METHOD)]
class TopLevelMenuItem extends MenuItem
{
    public function __construct(
        string $menuTitle,
        Capabilities $capability,
        string $slug,
        Dashicons|string $icon = '',
        ?int $position = null,
        string $subMenuTitle = ''
    ) {
        parent::__construct(
            $menuTitle,
            $capability,
            $slug,
            $icon,
            $position,
            '',
            $subMenuTitle
        );
    }
}
