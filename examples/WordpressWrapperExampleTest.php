<?php

/**
 * Example test demonstrating how to test code that uses the Wordpress wrapper class
 */

uses(\VAF\WP\FrameworkTests\TestCase::class);
use VAF\WP\Framework\Testing\Wordpress;

// Include the example class for testing
require_once __DIR__ . '/WordpressWrapperExample.php';

beforeEach(function () {
    // Reset any existing fakes before each test
    Wordpress::resetFake();
});

test('registerPlugin should return correct plugin information', function () {
    // Arrange: Set up mocks for WordPress functions
    Wordpress::fake();
    Wordpress::mock()
        ->shouldReceive('plugin_dir_url')
        ->with('/path/to/plugin/main.php')
        ->andReturn('https://example.com/wp-content/plugins/my-plugin/');
    
    Wordpress::mock()
        ->shouldReceive('plugin_dir_path')
        ->with('/path/to/plugin/main.php')
        ->andReturn('/var/www/wp-content/plugins/my-plugin/');
    
    Wordpress::mock()
        ->shouldReceive('plugin_basename')
        ->with('/path/to/plugin/main.php')
        ->andReturn('my-plugin/main.php');

    $manager = new PluginManager();

    // Act: Call the method under test
    $result = $manager->registerPlugin('/path/to/plugin/main.php');

    // Assert: Verify the result
    expect($result)->toEqual([
        'url' => 'https://example.com/wp-content/plugins/my-plugin/',
        'path' => '/var/www/wp-content/plugins/my-plugin/',
        'name' => 'my-plugin'
    ]);
});

test('isAdminPage should return true when in admin area', function () {
    // Arrange: Mock is_admin to return true
    Wordpress::fake();
    Wordpress::mock()
        ->shouldReceive('is_admin')
        ->andReturn(true);

    $manager = new PluginManager();

    // Act: Call the method under test
    $result = $manager->isAdminPage();

    // Assert: Verify the result
    expect($result)->toBeTrue();
});

test('isAdminPage should return false when not in admin area', function () {
    // Arrange: Mock is_admin to return false
    Wordpress::fake();
    Wordpress::mock()
        ->shouldReceive('is_admin')
        ->andReturn(false);

    $manager = new PluginManager();

    // Act: Call the method under test
    $result = $manager->isAdminPage();

    // Assert: Verify the result
    expect($result)->toBeFalse();
});

test('addCustomAction should register WordPress action', function () {
    // Arrange: Set up spy to verify add_action is called
    Wordpress::fake();
    
    $manager = new PluginManager();

    // Act: Call the method under test
    $manager->addCustomAction('my-plugin');

    // Assert: Verify add_action was called with correct parameters
    Wordpress::mock()
        ->shouldHaveReceived('add_action')
        ->with('init', \Mockery::type('Closure'));
});

test('getCurrentUser should return mocked user data', function () {
    // Arrange: Mock wp_get_current_user to return fake user data
    $fakeUser = (object) [
        'ID' => 123,
        'user_login' => 'testuser',
        'user_email' => 'test@example.com'
    ];
    
    Wordpress::fake();
    Wordpress::mock()
        ->shouldReceive('wp_get_current_user')
        ->andReturn($fakeUser);

    $manager = new PluginManager();

    // Act: Call the method under test
    $result = $manager->getCurrentUser();

    // Assert: Verify the result
    expect($result)->toBe($fakeUser);
    expect($result->ID)->toEqual(123);
    expect($result->user_login)->toEqual('testuser');
    expect($result->user_email)->toEqual('test@example.com');
});

test('can verify function call counts and arguments', function () {
    // Arrange: Set up mock with specific expectations
    Wordpress::fake();
    Wordpress::mock()
        ->shouldReceive('add_action')
        ->twice()
        ->withArgs(['init', \Mockery::type('Closure')])
        ->andReturnNull();

    $manager = new PluginManager();

    // Act: Call the method multiple times
    $manager->addCustomAction('plugin-1');
    $manager->addCustomAction('plugin-2');

    // Assert: Mockery will automatically verify the expectations
    // The twice() expectation will be checked automatically
});