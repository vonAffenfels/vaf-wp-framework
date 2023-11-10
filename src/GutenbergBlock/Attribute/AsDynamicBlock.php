<?php

namespace VAF\WP\Framework\GutenbergBlock\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsDynamicBlock
{
    public function __construct(
        public readonly string $blockType,
        public readonly string $editorScriptHandle,
        public readonly int $version = 2,
    )
    {
    }
}
