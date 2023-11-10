<?php

namespace VAF\WP\FrameworkTests\Unit;

use ReflectionClass;
use VAF\WP\Framework\GutenbergBlock\RendererDefinition;
use VAF\WP\FrameworkTests\TestCase;
use VAF\WP\FrameworkTests\Unit\RendererDefinition\ArrayHintedTestBlock;
use VAF\WP\FrameworkTests\Unit\RendererDefinition\PlainTestBlock;
use VAF\WP\FrameworkTests\Unit\RendererDefinition\TestAttributes;
use VAF\WP\FrameworkTests\Unit\RendererDefinition\TypehintedTestBlock;

class RendererDefinitionTest extends TestCase
{

    /**
     * @test
     */
    public function should_create_typehinted_renderer_for_typehinted_test_block()
    {
        $definition = RendererDefinition::fromClassReflection(new ReflectionClass(TypehintedTestBlock::class))->definition();

        $this->assertEquals('typehinted', $definition['type']);
        $this->assertEquals(TestAttributes::class, $definition['class']);
    }

    /**
     * @test
     */
    public function should_call_test_block_instance_with_instance_of_test_attributes()
    {
        $instance = new TypehintedTestBlock();
        $callback = RendererDefinition::fromClassReflection(new ReflectionClass(TypehintedTestBlock::class))->renderer($instance);

        $callback([], '');

        $this->assertInstanceOf(TestAttributes::class, $instance->calledWith);
    }

    /**
     * @test
     */
    public function should_be_able_to_read_attributes_from_class()
    {
        $typehintedTestBlock = new TypehintedTestBlock();
        $callback = RendererDefinition::fromClassReflection(new ReflectionClass(TypehintedTestBlock::class))->renderer($typehintedTestBlock);

        $callback([
            'test' => 'expected value'
        ], '');

        $this->assertEquals('expected value', $typehintedTestBlock->calledWith->test);
    }


    /**
     * @test
     */
    public function should_create_plain_renderer_for_plain_test_block()
    {
        $definition = RendererDefinition::fromClassReflection(new ReflectionClass(PlainTestBlock::class))->definition();

        $this->assertEquals('plain', $definition['type']);
    }

    /**
     * @test
     */
    public function should_call_instance_of_plain_test_block_with_block_attributes_as_array()
    {
        $plainTestBlock = new PlainTestBlock();
        $callback = RendererDefinition::fromClassReflection(new ReflectionClass(PlainTestBlock::class))->renderer($plainTestBlock);

        $callback(['expected key' => 'expected value'], '');

        $this->assertIsArray($plainTestBlock->calledWith);
        $this->assertArrayHasKey('expected key', $plainTestBlock->calledWith);
        $this->assertEquals('expected value', $plainTestBlock->calledWith['expected key']);
    }


    /**
     * @test
     */
    public function should_create_plain_renderer_for_array_hinted_test_block()
    {
        $definition = RendererDefinition::fromClassReflection(new ReflectionClass(ArrayHintedTestBlock::class))->definition();

        $this->assertEquals('plain', $definition['type']);
    }


    /**
     * @test
     */
    public function should_call_instance_of_array_hinted_test_block_with_block_attributes_as_array()
    {
        $plainTestBlock = new ArrayHintedTestBlock();
        $callback = RendererDefinition::fromClassReflection(new ReflectionClass(ArrayHintedTestBlock::class))->renderer($plainTestBlock);

        $callback(['expected key' => 'expected value'], '');

        $this->assertIsArray($plainTestBlock->calledWith);
        $this->assertArrayHasKey('expected key', $plainTestBlock->calledWith);
        $this->assertEquals('expected value', $plainTestBlock->calledWith['expected key']);
    }
}
