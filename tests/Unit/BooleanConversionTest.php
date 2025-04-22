<?php

uses(\VAF\WP\FrameworkTests\TestCase::class);
use VAF\WP\Framework\Setting\Conversion;

test('should convert string true in db to true boolean', function () {
    $result = (Conversion::boolean()->fromDb)('true');

    expect($result)->toBeTrue();
});

test('should convert string false in db to false boolean', function () {
    $result = (Conversion::boolean()->fromDb)('false');

    expect($result)->toBeFalse();
});

test('should convert string inactive in db to false boolean', function () {
    $result = (Conversion::boolean()->fromDb)('inactive');

    expect($result)->toBeFalse();
});

test('should convert string active in db to true boolean', function () {
    $result = (Conversion::boolean()->fromDb)('active');

    expect($result)->toBeTrue();
});

test('should convert string 1 in db to true boolean', function () {
    $result = (Conversion::boolean()->fromDb)('1');

    expect($result)->toBeTrue();
});

test('should convert string 0 in db to false boolean', function () {
    $result = (Conversion::boolean()->fromDb)('0');

    expect($result)->toBeFalse();
});

test('should convert input string active to true', function () {
    $result = (Conversion::boolean()->fromInput)('active');

    expect($result)->toBeTrue();
});

test('should convert input string inactive to false', function () {
    $result = (Conversion::boolean()->fromInput)('inactive');

    expect($result)->toBeFalse();
});

test('should convert input string true to true', function () {
    $result = (Conversion::boolean()->fromInput)('true');

    expect($result)->toBeTrue();
});

test('should convert input string false to false', function () {
    $result = (Conversion::boolean()->fromInput)('false');

    expect($result)->toBeFalse();
});

test('should convert true boolean value to true string in db', function () {
    $result = (Conversion::boolean()->toDb)(true);

    expect($result)->toEqual('true');
});

test('should convert false boolean value to false string in db', function () {
    $result = (Conversion::boolean()->toDb)(false);

    expect($result)->toEqual('false');
});
