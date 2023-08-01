<?php

namespace VAF\WP\Framework\Utils;

enum ThemeSearchMode: string
{
    case ALL = 'all';
    case PARENT_ONLY = 'parentOnly';
    case CURRENT_ONLY = 'currentOnly';
}
