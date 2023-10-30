<?php

namespace VAF\WP\Framework\GutenbergBlock;

use VAF\WP\Framework\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(private readonly WordpressKernel $kernel, private readonly array $gutenbergBlocks)
    {
    }

    public function registerBlocks(): void
    {
        foreach ($this->gutenbergBlocks as $serviceId => $hookContainer) {
            foreach ($hookContainer as $gutenbergBlock => $data) {

                register_block_type( $data['block_type'], array(
                    'api_version' => 2,
                    'editor_script' => $data['editor_script'],
                    'render_callback' => function(...$args) use ($serviceId) {
                        $this->kernel->getContainer()->get($serviceId)->render(...$args);
                    }
                ) );

            }
        }
    }
}
