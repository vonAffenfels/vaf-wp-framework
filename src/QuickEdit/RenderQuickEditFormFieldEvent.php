<?php

namespace VAF\WP\Framework\QuickEdit;

class RenderQuickEditFormFieldEvent
{
    public function __construct(public readonly string $columnName)
    {
    }
}
