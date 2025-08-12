<?php

uses(\VAF\WP\FrameworkTests\TestCase::class);
use VAF\WP\Framework\GutenbergBlock\RendererDefinition;
use VAF\WP\FrameworkTests\Unit\RendererDefinition\ArrayHintedTestBlock;
use VAF\WP\FrameworkTests\Unit\RendererDefinition\PlainTestBlock;
use VAF\WP\FrameworkTests\Unit\RendererDefinition\TestAttributes;
use VAF\WP\FrameworkTests\Unit\RendererDefinition\TypehintedTestBlock;

test('should create typehinted renderer for typehinted test block', function () {
    $definition = RendererDefinition::fromClassReflection(new ReflectionClass(TypehintedTestBlock::class))->definition();

    expect($definition['type'])->toEqual('typehinted');
    expect($definition['class'])->toEqual(TestAttributes::class);
});

test('should call test block instance with instance of test attributes', function () {
    $instance = new TypehintedTestBlock();
    $callback = RendererDefinition::fromClassReflection(new ReflectionClass(TypehintedTestBlock::class))->renderer($instance);

    $callback([], '');

    expect($instance->calledWith)->toBeInstanceOf(TestAttributes::class);
});

test('should be able to read attributes from class', function () {
    $typehintedTestBlock = new TypehintedTestBlock();
    $callback = RendererDefinition::fromClassReflection(new ReflectionClass(TypehintedTestBlock::class))->renderer($typehintedTestBlock);

    $callback([
        'test' => 'expected value'
    ], '');

    expect($typehintedTestBlock->calledWith->test)->toEqual('expected value');
});

test('should create plain renderer for plain test block', function () {
    $definition = RendererDefinition::fromClassReflection(new ReflectionClass(PlainTestBlock::class))->definition();

    expect($definition['type'])->toEqual('plain');
});

test('should call instance of plain test block with block attributes as array', function () {
    $plainTestBlock = new PlainTestBlock();
    $callback = RendererDefinition::fromClassReflection(new ReflectionClass(PlainTestBlock::class))->renderer($plainTestBlock);

    $callback(['expected key' => 'expected value'], '');

    expect($plainTestBlock->calledWith)->toBeArray();
    expect($plainTestBlock->calledWith)->toHaveKey('expected key');
    expect($plainTestBlock->calledWith['expected key'])->toEqual('expected value');
});

test('should create plain renderer for array hinted test block', function () {
    $definition = RendererDefinition::fromClassReflection(new ReflectionClass(ArrayHintedTestBlock::class))->definition();

    expect($definition['type'])->toEqual('plain');
});

test('should call instance of array hinted test block with block attributes as array', function () {
    $plainTestBlock = new ArrayHintedTestBlock();
    $callback = RendererDefinition::fromClassReflection(new ReflectionClass(ArrayHintedTestBlock::class))->renderer($plainTestBlock);

    $callback(['expected key' => 'expected value'], '');

    expect($plainTestBlock->calledWith)->toBeArray();
    expect($plainTestBlock->calledWith)->toHaveKey('expected key');
    expect($plainTestBlock->calledWith['expected key'])->toEqual('expected value');
});
