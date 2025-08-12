# Facade

The Facade system provides a convenient static interface to services in the dependency injection container, inspired by Laravel's facade pattern. This allows you to access services using static method calls while maintaining the benefits of dependency injection and testability.

## Key Features

- Static access to container services
- Lazy loading - services are only instantiated when first accessed
- Automatic class aliasing for cleaner syntax
- Caching of resolved instances for performance
- Full IDE autocompletion support when properly documented

## Basic Usage

To create a facade for a service:

1. Create a facade class extending `Facade`
2. Add the `#[AsFacade]` attribute specifying the service class
3. The facade will automatically proxy all method calls to the service

```php
use VAF\WP\Framework\Facade\Facade;
use VAF\WP\Framework\Facade\Attribute\AsFacade;

// Your service class
class UserService {
    public function __construct(
        private readonly DatabaseConnection $db,
        private readonly CacheInterface $cache
    ) {}
    
    public function getUser(int $id): ?User {
        // Implementation
    }
    
    public function updateUser(int $id, array $data): bool {
        // Implementation
    }
}

// Create a facade for the service
#[AsFacade(UserService::class)]
class UserServiceFacade extends Facade {
}

// Use the facade statically anywhere in your code
$user = UserServiceFacade::getUser(123);
UserServiceFacade::updateUser(123, ['name' => 'John Doe']);
```

## IDE Autocompletion

For better IDE support, you can add PHPDoc comments to your facade class:

```php
/**
 * @method static ?User getUser(int $id)
 * @method static bool updateUser(int $id, array $data)
 * @method static User[] getAllUsers()
 * 
 * @see UserService
 */
#[AsFacade(UserService::class)]
class UserServiceFacade extends Facade {
}
```

## Service Registration

The service being accessed through the facade must be registered in the container. You can do this in your `services.yaml`:

```yaml
services:
    App\Services\UserService:
        arguments:
            - '@App\Database\DatabaseConnection'
            - '@cache.interface'
```

Or use autowiring if your service supports it:

```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true
    
    App\Services\UserService: ~
```

## How It Works

1. When you call a static method on a facade, PHP's `__callStatic` magic method is triggered
2. The facade looks up the `#[AsFacade]` attribute to find the service class
3. It retrieves the service instance from the container (or cache if already resolved)
4. The method call is forwarded to the service instance with all arguments
5. The result is returned to the caller

## Caching and Performance

- Service instances are cached after first resolution
- Subsequent calls use the cached instance for better performance
- You can clear the cache if needed:

```php
// Clear all cached facade instances
Facade::clearResolvedInstances();

// Clear a specific cached instance
Facade::clearResolvedInstance(UserService::class);
```

## Testing with Facades

Facades are fully testable. In your tests, you can:

```php
// Mock the underlying service
$mockUserService = $this->createMock(UserService::class);
$mockUserService->method('getUser')
    ->with(123)
    ->willReturn(new User(['id' => 123, 'name' => 'Test User']));

// Replace the service in the container
$container->set(UserService::class, $mockUserService);

// The facade will now use your mock
$user = UserServiceFacade::getUser(123);
```

## Best Practices

1. **Use facades for frequently accessed services** - They're ideal for services you use throughout your application
2. **Keep the underlying service testable** - Design your services with dependency injection in mind
3. **Document facade methods** - Add PHPDoc comments for better IDE support
4. **Consider performance** - While facades add minimal overhead, direct dependency injection may be better for performance-critical paths
5. **Avoid overuse** - Not every service needs a facade; use them where they genuinely improve code readability

## Common Use Cases

Facades work particularly well for:

- Configuration services
- Cache services
- Logging services
- Database query builders
- Authentication/authorization services
- File system operations
- Email services
- Notification services

## Example: Complete Implementation

Here's a complete example of a cache service with a facade:

```php
// Service class
namespace App\Services;

class CacheService {
    public function __construct(
        private readonly CachePoolInterface $pool
    ) {}
    
    public function get(string $key, mixed $default = null): mixed {
        $item = $this->pool->getItem($key);
        return $item->isHit() ? $item->get() : $default;
    }
    
    public function set(string $key, mixed $value, int $ttl = 3600): bool {
        $item = $this->pool->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);
        return $this->pool->save($item);
    }
    
    public function forget(string $key): bool {
        return $this->pool->deleteItem($key);
    }
}

// Facade class
namespace App\Facades;

use VAF\WP\Framework\Facade\Facade;
use VAF\WP\Framework\Facade\Attribute\AsFacade;
use App\Services\CacheService;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool set(string $key, mixed $value, int $ttl = 3600)
 * @method static bool forget(string $key)
 * 
 * @see CacheService
 */
#[AsFacade(CacheService::class)]
class Cache extends Facade {
}

// Usage anywhere in your application
use App\Facades\Cache;

// Store a value
Cache::set('user.123', $userData, 7200);

// Retrieve a value
$userData = Cache::get('user.123', []);

// Delete a value
Cache::forget('user.123');
```
