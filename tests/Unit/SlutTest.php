<?php

uses(\VAF\WP\FrameworkTests\TestCase::class);
use VAF\WP\Framework\Slug;

test('should keep a to z', function () {
    $slug = Slug::fromName('abcdefghijklmnopqrstuvwxyz');

    expect((string)$slug)->toEqual('abcdefghijklmnopqrstuvwxyz');
});

test('should change capital a to z to lowercase a to z', function () {
    $slug = Slug::fromName('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

    expect((string)$slug)->toEqual('abcdefghijklmnopqrstuvwxyz');
});

test('should change anything not a to z to dash', function () {
    $slug = Slug::fromName('a b');

    expect((string)$slug)->toEqual('a-b');
});

test('should reduce multiple dashes to single dash', function () {
    $slug = Slug::fromName('a!@#b');

    expect((string)$slug)->toEqual('a-b');
});
