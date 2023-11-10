<?php

namespace VAF\WP\FrameworkTests\Unit\RendererDefinition;

class TypehintedTestBlock
{

    public ?TestAttributes $calledWith = null;

    public function render(TestAttributes $attributes, string $content): string
    {
        $this->calledWith = $attributes;

        return '';
    }

}
