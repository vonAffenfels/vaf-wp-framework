<?php

namespace VAF\WP\Framework\Metabox\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Metabox
{
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_CORE = 'core';
    public const PRIORITY_DEFAULT = 'default';
    public const PRIORITY_LOW = 'low';

    public const CONTEXT_NORMAL = 'normal';
    public const CONTEXT_SIDE = 'side';
    public const CONTEXT_ADVANCED = 'advanced';

    public function __construct(
        public readonly string $title,
        public readonly ?string $id = null,
        public readonly string|array|null $screen = null,
        public readonly string|null $supporting = null,
        public readonly string $context = self::CONTEXT_ADVANCED,
        public readonly string $priority = self::PRIORITY_DEFAULT,
    ) {
    }
}
