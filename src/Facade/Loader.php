<?php

namespace VAF\WP\Framework\Facade;

use VAF\WP\Framework\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(
        private readonly WordpressKernel $kernel,
        private readonly array $facades
    ) {
    }

    public function registerFacades(): void
    {
        Facade::setKernel($this->kernel);

        foreach ($this->facades as $facadeClass => $facadeData) {
            if (!class_exists($facadeClass)) {
                continue;
            }

            class_alias($facadeClass, $facadeData['alias']);
        }
    }
}
