<?php

namespace VAF\WP\Framework\RestAPI;

use VAF\WP\Framework\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(private readonly WordpressKernel $kernel, private readonly array $restContainer)
    {
    }

    public function registerRestRoutes(): void
    {
    }
}
