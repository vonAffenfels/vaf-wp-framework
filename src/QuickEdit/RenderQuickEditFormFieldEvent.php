<?php

namespace VAF\WP\Framework\QuickEdit;

class RenderQuickEditFormFieldEvent
{

    public function __construct(public readonly int $postId, public readonly string $columnName)
    {
    }

}
