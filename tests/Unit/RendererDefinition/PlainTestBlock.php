<?php

namespace VAF\WP\FrameworkTests\Unit\RendererDefinition;

class PlainTestBlock
{
    public mixed $calledWith = null;

    public function render($blockAttributes, string $content): string
    {
        $this->calledWith = $blockAttributes;

        return $content;
    }
}
