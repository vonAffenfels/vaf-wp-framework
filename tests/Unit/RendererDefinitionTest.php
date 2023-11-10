<?php

namespace VAF\WP\FrameworkTests\Unit;

use ReflectionClass;
use VAF\WP\Framework\GutenbergBlock\RendererDefinition;
use VAF\WP\FrameworkTests\TestCase;
use VAF\WP\FrameworkTests\Unit\RendererDefinition\TestAttributes;
use VAF\WP\FrameworkTests\Unit\RendererDefinition\TestBlock;

class RendererDefinitionTest extends TestCase
{

    /**
     * @test
     */
    public function should_create_typehinted_renderer_for_test_block()
    {
        $definition = RendererDefinition::fromClassReflection(new ReflectionClass(TestBlock::class))->definition();

        $this->assertEquals('typehinted', $definition['type']);
        $this->assertEquals(TestAttributes::class, $definition['class']);
    }

    /**
     * @test
     */
    public function should_call_test_block_instance_with_instance_of_test_attributes()
    {
        $instance = new TestBlock();
        $callback = RendererDefinition::fromClassReflection(new ReflectionClass(TestBlock::class))->renderer($instance);

        $callback([], '');

        $this->assertInstanceOf(TestAttributes::class, $instance->calledWith);
    }

    /**
     * @test
     */
    public function should_be_able_to_read_attributes_from_class()
    {
        $instance = new TestBlock();
        $callback = RendererDefinition::fromClassReflection(new ReflectionClass(TestBlock::class))->renderer($instance);

        $callback([
            'test' => 'expected value'
        ], '');

        $this->assertEquals('expected value', $instance->calledWith->test);
    }
}
