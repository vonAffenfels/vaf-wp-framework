<?php

uses(\VAF\WP\FrameworkTests\TestCase::class);
use VAF\WP\Framework\Metabox\EmptySupportingScreensException;
use VAF\WP\Framework\Metabox\ScreenList;

test('should return screen if supported is null', function () {
    $screens = ScreenList::fromScreen('expected screen')
        ->withSupporting(null, fn() => [])
        ->screens();

    expect($screens)->toEqual('expected screen');
});

test('should return screen and supporting post type in array', function () {
    $screens = ScreenList::fromScreen('expected screen')
        ->withSupporting('feature', fn() => ['expected post type'])
        ->screens();

    expect($screens)->toEqual(['expected screen', 'expected post type']);
});

test('should only return supporting post type in array if screen is null', function () {
    $screens = ScreenList::fromScreen(null)
        ->withSupporting('feature', fn() => ['expected post type'])
        ->screens();

    expect($screens)->toEqual(['expected post type']);
});

test('should throw is empty exception if screen is null and supporting screens are empty', function () {
    $this->expectException(EmptySupportingScreensException::class);
    ScreenList::fromScreen(null)
        ->withSupporting('feature', fn() => [])
        ->screens();
});

test('should return null for screen null and no supporting given', function () {
    $screens = ScreenList::fromScreen(null)
        ->withSupporting(null, fn(string $feature) => [])
        ->screens();

    expect($screens)->toEqual(null);
});
