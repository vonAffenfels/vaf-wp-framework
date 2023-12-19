<?php

namespace VAF\WP\Framework\Metabox\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Metabox
{
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CORE = 'core';
    const PRIORITY_DEFAULT = 'default';
    const PRIORITY_LOW = 'low';

    const CONTEXT_NORMAL = 'normal';
    const CONTEXT_SIDE = 'side';
    const CONTEXT_ADVANCED = 'advanced';

    public function __construct(
        public readonly string $title,
        public readonly ?string $id = null,
        public readonly string|array|null $screen = null,
        public readonly string $context = self::CONTEXT_ADVANCED,
        public readonly string $priority = self::PRIORITY_DEFAULT,
        public readonly string|null $supporting = null,
    ) {
    }

}
