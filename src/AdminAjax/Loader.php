<?php

namespace VAF\WP\Framework\AdminAjax;

use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(
        private readonly WordpressKernel $kernel,
        private readonly BaseWordpress $base,
        private readonly array $adminAjaxContainer
    ) {
    }

    public function registerRestRoutes(): void
    {
        $name = $this->base->getName();

        foreach ($this->adminAjaxContainer as $serviceId => $adminAjaxContainer) {
        }
    }
}
