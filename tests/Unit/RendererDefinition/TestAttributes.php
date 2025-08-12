<?php

namespace VAF\WP\FrameworkTests\Unit\RendererDefinition;

class TestAttributes
{

    public readonly string $test;

    public static function fromBlockAttributes($blockAttributes): self
    {
        $testAttributes = new static();

        $testAttributes->test = $blockAttributes['test'] ?? 'not set';

        return $testAttributes;
    }
}
