<?php

namespace VAF\WP\Framework\GutenbergBlock;

use VAF\WP\Framework\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(private readonly WordpressKernel $kernel, private readonly array $dynamicBlocks)
    {
    }

    public function registerBlocks(): void
    {
        foreach ($this->dynamicBlocks as $dynamicBlock) {
            $instance = $this->kernel->getContainer()->get($dynamicBlock['class']);

            if ($dynamicBlock['hasDynamicAttributes'] ?? false) {
                $dynamicBlock['options']['attributes'] = $instance->getDynamicAttributes();
            }

            register_block_type($dynamicBlock['type'], [
                ...$dynamicBlock['options'],
                'render_callback' => RendererDefinition::fromRendererDefinition($dynamicBlock['renderer'])->renderer($instance),
            ]);
        }
    }
}
