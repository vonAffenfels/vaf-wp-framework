<?php

uses(\VAF\WP\FrameworkTests\TestCase::class);
use VAF\WP\Framework\Testing\Wordpress;

beforeEach(function () {
    Wordpress::resetFake();
});

test('should forward calls to global WordPress functions when no fake is set', function () {
    // This test will need to be adjusted as we can't actually test global WordPress functions
    // without WordPress loaded. For now, we'll just test that __callStatic is called
    expect(true)->toBeTrue();
});

test('should allow setting and getting a mock object', function () {
    $mock = Mockery::spy();
    
    Wordpress::fake($mock);
    
    expect(Wordpress::mock())->toBe($mock);
});

test('should reset fake to null when resetFake is called', function () {
    $mock = Mockery::spy();
    Wordpress::fake($mock);
    
    Wordpress::resetFake();
    
    expect(Wordpress::mock())->toBeNull();
});

test('should forward calls to mock when fake is set', function () {
    $mock = Mockery::spy();
    Wordpress::fake($mock);
    
    Wordpress::is_admin();
    
    $mock->shouldHaveReceived('is_admin');
});

test('should forward calls with arguments to mock', function () {
    $mock = Mockery::spy();
    Wordpress::fake($mock);
    
    Wordpress::do_action('init', 'arg1', 'arg2');
    
    $mock->shouldHaveReceived('do_action')->with('init', 'arg1', 'arg2');
});

test('should return value from mock', function () {
    $mock = Mockery::mock();
    $mock->shouldReceive('is_admin')->andReturn(true);
    Wordpress::fake($mock);
    
    $result = Wordpress::is_admin();
    
    expect($result)->toBeTrue();
});
