# Testing with WordPress Functions

This document explains how to make WordPress function calls testable using the framework's `Wordpress` wrapper class.

## The Problem

WordPress provides its functionality through global functions like `is_admin()`, `add_action()`, `wp_get_current_user()`, etc. These functions are difficult to test because:

1. They require WordPress to be loaded during tests (slow)
2. They can't be easily mocked or stubbed
3. They make unit tests dependent on WordPress state
4. They prevent true isolation in unit tests

## The Solution: Wordpress Wrapper Class

The framework provides a `Wordpress` class in `VAF\WP\Framework\Testing\Wordpress` that acts as a proxy to WordPress global functions. This enables easy mocking and testing without requiring WordPress to be loaded.

### How It Works

The `Wordpress` class uses PHP's `__callStatic` magic method to forward method calls to WordPress global functions or to a mock object when testing.

```php
// Instead of calling WordPress functions directly:
if (is_admin()) {
    add_action('admin_init', $callback);
}

// Use the Wordpress wrapper:
use VAF\WP\Framework\Testing\Wordpress;

if (Wordpress::is_admin()) {
    Wordpress::add_action('admin_init', $callback);
}
```

## Testing API

### Wordpress::fake(?object $mock = null)

Sets up a mock object to receive WordPress function calls. If no mock is provided, creates a default Mockery spy.

```php
// Create a default spy
Wordpress::fake();

// Or provide your own mock
$mock = Mockery::mock();
Wordpress::fake($mock);
```

### Wordpress::resetFake()

Removes the fake mock and restores normal WordPress function forwarding.

```php
Wordpress::resetFake();
```

### Wordpress::mock(): ?object

Returns the current mock object for setting expectations.

```php
Wordpress::fake();
Wordpress::mock()->shouldReceive('is_admin')->andReturn(true);
```

## Usage Patterns

### Basic Mocking

```php
test('should check if user is in admin', function () {
    // Arrange
    Wordpress::fake();
    Wordpress::mock()
        ->shouldReceive('is_admin')
        ->andReturn(true);
    
    $service = new MyService();
    
    // Act
    $result = $service->doSomething();
    
    // Assert
    expect($result)->toContain('admin content');
});
```

### Verifying Function Calls

```php
test('should register WordPress action', function () {
    // Arrange
    Wordpress::fake();
    
    $service = new MyService();
    
    // Act
    $service->init();
    
    // Assert
    Wordpress::mock()
        ->shouldHaveReceived('add_action')
        ->with('init', Mockery::type('Closure'));
});
```

### Multiple Function Calls

```php
test('should handle plugin registration', function () {
    // Arrange
    Wordpress::fake();
    Wordpress::mock()
        ->shouldReceive('plugin_dir_url')
        ->with('/path/to/plugin.php')
        ->andReturn('https://example.com/wp-content/plugins/my-plugin/');
    
    Wordpress::mock()
        ->shouldReceive('plugin_dir_path')
        ->with('/path/to/plugin.php')
        ->andReturn('/var/www/wp-content/plugins/my-plugin/');
    
    $service = new PluginManager();
    
    // Act
    $result = $service->registerPlugin('/path/to/plugin.php');
    
    // Assert
    expect($result['url'])->toEqual('https://example.com/wp-content/plugins/my-plugin/');
    expect($result['path'])->toEqual('/var/www/wp-content/plugins/my-plugin/');
});
```

### Complex Expectations

```php
test('should call functions with specific arguments and counts', function () {
    // Arrange
    Wordpress::fake();
    Wordpress::mock()
        ->shouldReceive('add_action')
        ->twice()
        ->withArgs(['init', Mockery::type('Closure')])
        ->andReturnNull();
    
    Wordpress::mock()
        ->shouldReceive('is_admin')
        ->once()
        ->withNoArgs()
        ->andReturn(true);
    
    $service = new MyService();
    
    // Act
    $service->setupHooks();
    $service->setupHooks(); // Called twice
    $service->checkAdmin();
    
    // Assert - Mockery verifies expectations automatically
});
```

## Test Setup

For consistent test behavior, always reset fakes in your test setup:

```php
beforeEach(function () {
    Wordpress::resetFake();
});
```

Or extend the framework's `TestCase` which already includes this setup:

```php
uses(\VAF\WP\FrameworkTests\TestCase::class);
```

## Migration Guide

### Before (Untestable)

```php
class UserService
{
    public function getCurrentUserInfo(): array
    {
        if (!is_user_logged_in()) {
            return [];
        }
        
        $user = wp_get_current_user();
        
        return [
            'id' => $user->ID,
            'name' => $user->display_name,
            'is_admin' => current_user_can('manage_options')
        ];
    }
}
```

### After (Testable)

```php
use VAF\WP\Framework\Testing\Wordpress;

class UserService
{
    public function getCurrentUserInfo(): array
    {
        if (!Wordpress::is_user_logged_in()) {
            return [];
        }
        
        $user = Wordpress::wp_get_current_user();
        
        return [
            'id' => $user->ID,
            'name' => $user->display_name,
            'is_admin' => Wordpress::current_user_can('manage_options')
        ];
    }
}
```

### Test for the Refactored Code

```php
test('getCurrentUserInfo returns user data when logged in', function () {
    // Arrange
    $fakeUser = (object) ['ID' => 123, 'display_name' => 'John Doe'];
    
    Wordpress::fake();
    Wordpress::mock()
        ->shouldReceive('is_user_logged_in')
        ->andReturn(true);
    
    Wordpress::mock()
        ->shouldReceive('wp_get_current_user')
        ->andReturn($fakeUser);
    
    Wordpress::mock()
        ->shouldReceive('current_user_can')
        ->with('manage_options')
        ->andReturn(false);
    
    $service = new UserService();
    
    // Act
    $result = $service->getCurrentUserInfo();
    
    // Assert
    expect($result)->toEqual([
        'id' => 123,
        'name' => 'John Doe',
        'is_admin' => false
    ]);
});

test('getCurrentUserInfo returns empty array when not logged in', function () {
    // Arrange
    Wordpress::fake();
    Wordpress::mock()
        ->shouldReceive('is_user_logged_in')
        ->andReturn(false);
    
    $service = new UserService();
    
    // Act
    $result = $service->getCurrentUserInfo();
    
    // Assert
    expect($result)->toEqual([]);
});
```

## Best Practices

1. **Always reset fakes**: Use `beforeEach()` or extend `TestCase`
2. **Be explicit with expectations**: Use `shouldReceive()` with specific arguments
3. **Test both positive and negative cases**: Mock functions to return different values
4. **Verify call counts**: Use `once()`, `twice()`, `times(n)` when appropriate
5. **Use appropriate return values**: Mock realistic WordPress data structures
6. **Gradual migration**: Convert existing code incrementally
7. **Keep mocks simple**: Don't over-complicate mock setups

## Examples

See the complete examples in:
- `examples/WordpressWrapperExample.php` - Usage examples
- `examples/WordpressWrapperExampleTest.php` - Testing examples
- `tests/Unit/WordpressTest.php` - Wrapper class tests

## Limitations

- Performance overhead: Minimal `__callStatic` overhead (negligible in practice)
- Debugging: One extra stack frame (still shows original call location)
- Migration effort: Existing code needs to be updated to use wrapper

The benefits of improved testability far outweigh these minor limitations.