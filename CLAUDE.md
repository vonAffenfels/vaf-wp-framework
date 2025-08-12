# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the vonAffenfels WordPress Framework - a PHP framework that simplifies WordPress plugin and theme development using Symfony components and modern PHP practices.

## Development Commands

### Testing
- Run all tests: `composer test` or `phpunit`
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

3. **Component Loaders**
   Each major feature has a Loader class and CompilerPass:
   - Hooks: `HookLoader` + `HookLoaderCompilerPass`
   - Metaboxes: `MetaboxLoader` + `MetaboxLoaderCompilerPass`
   - REST API: `RestAPILoader` + `RestAPILoaderCompilerPass`
   - etc.

4. **Template System** (`src/TemplateRenderer/`)
   - Supports both Twig and PHTML templates
   - Templates stored in `templates/` directory
   - Global context and function handlers for template data

5. **Post Objects** (`src/PostObjects/`)
   - Object-oriented wrapper for WordPress posts
   - Extensible via `#[PostTypeExtension]` attribute
   - Built-in support for Pages, Posts, and Nav Menu Items

## Key Development Patterns

1. **Service Registration**: Services are registered using Symfony DI container with attribute-based autoconfiguration
2. **WordPress Integration**: The framework boots during WordPress initialization and registers all components via appropriate WordPress hooks
3. **Template Rendering**: Use `TemplateRenderer` service to render templates with proper context
4. **Admin AJAX**: Admin AJAX actions are registered via attributes and handled through a unified loader
5. **Settings**: Framework provides a `Setting` base class with conversion support for handling WordPress options

## Entry Points

- Plugins extend `Plugin` class and call `Plugin::registerPlugin($file)` 
- Themes extend `Theme` class and call `Theme::registerTheme($path)`
- Container building: `Plugin::buildContainer()` for development