<?php

namespace VAF\WP\Framework\QuickEdit;

class QuickEditPostDataEvent
{
    public function __construct(public readonly int $postId, public readonly string $columnName)
    {
    }
}
