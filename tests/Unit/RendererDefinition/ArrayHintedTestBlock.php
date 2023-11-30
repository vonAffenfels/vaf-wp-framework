<?php

namespace VAF\WP\FrameworkTests\Unit\RendererDefinition;

class ArrayHintedTestBlock
{
    public ?array $calledWith = null;

    public function render(array $blockAttributes, string $content): string
    {
        $this->calledWith = $blockAttributes;

        return $content;
    }
}
