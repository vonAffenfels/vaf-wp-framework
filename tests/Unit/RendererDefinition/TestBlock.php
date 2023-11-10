<?php

namespace VAF\WP\FrameworkTests\Unit\RendererDefinition;

class TestBlock
{

    public ?TestAttributes $calledWith = null;

    public function render(TestAttributes $attributes, string $content): string
    {
        $this->calledWith = $attributes;

        return '';
    }

}
