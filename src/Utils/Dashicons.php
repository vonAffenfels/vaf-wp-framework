<?php

namespace VAF\WP\Framework\Utils;

enum Dashicons: string
{
    # Empty
    case NONE = 'none';

    # Admin Menu
    case ADMIN_CUSTOMIZER = 'dashicons-admin-customizer';

    # Welcome Screen
    case WELCOME_VIEW_SITE = 'dashicons-welcome-view-site';

    # Miscellaneous
    case MISC_SEARCH = 'dashicons-search';
}
