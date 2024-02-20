<?php

namespace VAF\WP\Framework\NavMenus;

class Loader
{
    public function __construct(private readonly array $navMenus)
    {
    }

    public function registerNavMenus(): void
    {
        if (!empty($this->navMenus)) {
            register_nav_menus($this->navMenus);
        }
    }
}
