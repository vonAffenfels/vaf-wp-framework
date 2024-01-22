<?php

namespace VAF\WP\Framework\Filter;

class WordpressFilters
{
    public function resultFrom($name, ...$args)
    {
        return apply_filters($name, ...$args);
    }
}
