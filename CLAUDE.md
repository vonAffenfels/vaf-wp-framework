# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the vonAffenfels WordPress Framework - a PHP framework that simplifies WordPress plugin and theme development using Symfony components and modern PHP practices.

## Development Commands

### Testing
- Run all tests: `composer test` or `./vendor/bin/pest`
- Run specific test file: `./vendor/bin/pest tests/Unit/ExampleTest.php`
- The framework uses Pest PHP testing framework

### Code Quality
- Check code style: `composer codestyle`
- Fix code style: `composer fixstyle`
- Code standards: PSR-12 for src/, custom rules for tests/

### Dependencies
- Install dependencies: `composer install`
- The framework requires PHP >= 8.1

## Architecture Overview

### Core Structure

The framework is built around a Symfony-based kernel system that provides dependency injection and service container functionality for WordPress:

1. **Kernel System** (`src/Kernel/`)
   - `WordpressKernel` - Base kernel that registers all framework services
   - `PluginKernel` - For WordPress plugins
   - `ThemeKernel` - For WordPress themes
   - Services configured via `config/services.yaml`

2. **Attribute-Based Registration**
   The framework uses PHP 8 attributes for service discovery and registration:
   - `#[AsHookContainer]` - Register WordPress hooks
   - `#[AsMetaboxContainer]` - Register metaboxes
   - `#[AsDynamicBlock]` - Register Gutenberg blocks
   - `#[AsRestContainer]` - Register REST API routes
   - `#[AsAdminAjaxContainer]` - Register admin AJAX handlers
   - `#[PostType]` - Register custom post types
   - `#[AsFacade]` - Register facades for static access to services

3. **Component Loaders**
   Each major feature has a Loader class and CompilerPass:
   - Hooks: `HookLoader` + `HookLoaderCompilerPass`
   - Metaboxes: `MetaboxLoader` + `MetaboxLoaderCompilerPass`
   - REST API: `RestAPILoader` + `RestAPILoaderCompilerPass`
   - Facades: `FacadeLoader` + `FacadeLoaderCompilerPass`
   - etc.

4. **Template System** (`src/TemplateRenderer/`)
   - Supports both Twig and PHTML templates
   - Templates stored in `templates/` directory
   - Global context and function handlers for template data

5. **Post Objects** (`src/PostObjects/`)
   - Object-oriented wrapper for WordPress posts
   - Extensible via `#[PostTypeExtension]` attribute
   - Built-in support for Pages, Posts, and Nav Menu Items

6. **Facade System** (`src/Facade/`)
   - Laravel-style facades for static access to container services
   - Lazy loading - services are instantiated only when first accessed
   - Automatic class aliasing for clean syntax
   - Cache resolved instances for performance

7. **Testing System** (`src/Wordpress/`)
   - `Wordpress` wrapper class for mocking WordPress global functions
   - Enables testable code without loading WordPress environment
   - Uses `__callStatic` magic method to proxy function calls
   - Provides `fake()`, `resetFake()`, `mock()` methods for easy mocking

## Key Development Patterns

1. **Service Registration**: Services are registered using Symfony DI container with attribute-based autoconfiguration
2. **WordPress Integration**: The framework boots during WordPress initialization and registers all components via appropriate WordPress hooks
3. **Template Rendering**: Use `TemplateRenderer` service to render templates with proper context
4. **Admin AJAX**: Admin AJAX actions are registered via attributes and handled through a unified loader
5. **Settings**: Framework provides a `Setting` base class with conversion support for handling WordPress options
   - Uses `Wordpress` wrapper class for all WordPress function calls (get_option, update_option)
   - Provides built-in fake system for high-level testing with `Setting::fakeSetting()` and `Setting::clearFakes()`
   - Supports conversions between database and application formats
6. **Facades**: Use `#[AsFacade(ServiceClass::class)]` attribute on facade classes extending `Facade` for static access to services
7. **Testing**: Use `Wordpress::function_name()` instead of direct WordPress function calls to enable mocking in tests

### Facade Usage Example

```php
// Define a service
class UserService {
    public function __construct(
        private readonly DatabaseConnection $db,
        private readonly CacheInterface $cache
    ) {}
    
    public function getUser(int $id): ?User {
        // Implementation
    }
}

// Create a facade for the service
use VAF\WP\Framework\Facade\Facade;
use VAF\WP\Framework\Facade\Attribute\AsFacade;

#[AsFacade(UserService::class)]
class UserServiceFacade extends Facade {
}

// Use the facade statically
$user = UserServiceFacade::getUser(123);
```

The facade will automatically resolve `UserService` from the container with all its dependencies when first accessed.

### Testing WordPress Functions Example

```php
// Instead of direct WordPress function calls (untestable):
if (is_admin()) {
    add_action('admin_init', $callback);
}

// Use the Wordpress wrapper (testable):
use VAF\WP\Framework\Wordpress\Wordpress;

if (Wordpress::is_admin()) {
    Wordpress::add_action('admin_init', $callback);
}

// In tests:
Wordpress::fake();
Wordpress::mock()->shouldReceive('is_admin')->andReturn(true);
```

The wrapper automatically forwards calls to WordPress functions in production but allows easy mocking during testing.

### Settings Testing Example

The Settings system provides two complementary testing approaches:

```php
// 1. High-level testing with Setting fakes (easy for simple scenarios):
Setting::fakeSetting('my_option', 'fake_value');
$setting = new MySetting('my_option', 'plugin_name');
expect($setting->getValue())->toBe('fake_value');

// 2. Lower-level WordPress function mocking (for integration testing):
Wordpress::fake();
Wordpress::mock()->shouldReceive('get_option')
    ->with('plugin_name_my_option', 'default')
    ->andReturn('db_value');
$setting = new MySetting('my_option', 'plugin_name', 'default');
expect($setting->getValue())->toBe('db_value');

// Always clean up in tests:
beforeEach(function () {
    Setting::clearFakes();
    Wordpress::resetFake();
});
```

## Entry Points

- Plugins extend `Plugin` class and call `Plugin::registerPlugin($file)` 
- Themes extend `Theme` class and call `Theme::registerTheme($path)`
- Container building: `Plugin::buildContainer()` for development

## Rules

- When writing a new system add a new docs/{system name}.md file similar to the files already in the docs directory and
  add a link to this file in the README.md
- When changing an existing system, update the corresponding docs/{system name}.md file

## Testing Best Practices

- **Always use the Wordpress wrapper class** for WordPress functions to ensure testability
- **Follow TDD cycle**: Write test first (red), implement functionality (green), then refactor
- **Use appropriate testing level**: 
  - Setting fakes for high-level behavior testing
  - Wordpress mocks for WordPress integration testing
- **Clean up after tests**: Always reset fakes and mocks in beforeEach/afterEach hooks
- **Mock at the right level**: Don't over-mock; use the simplest approach that tests your code properly
