<?php

namespace VAF\WP\Framework\QuickEdit;

class QuickEditPostDataEvent
{

    public function __construct(public readonly string $columnName)
    {
    }

}
