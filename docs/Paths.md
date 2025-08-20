# Paths

The Paths system provides a centralized way to handle file paths and URLs within your WordPress plugin or theme. It simplifies working with file system paths and their corresponding public URLs, making it easy to reference assets, read file contents, and generate URLs.

## Key Features

- Unified interface for file system paths and public URLs
- Type-safe path objects with helpful methods
- Automatic path normalization across different operating systems
- Built-in file operations with proper error handling
- Dependency injection support via the service container

## Basic Usage

The Paths service can be injected into any class that needs to work with file paths:

```php
use VAF\WP\Framework\Paths\Paths;

class AssetManager
{
    public function __construct(
        private readonly Paths $paths
    ) {}

    public function getImageUrl(string $imageName): string
    {
        $path = $this->paths->fromPluginRoot('resources/images/' . $imageName);
        return $path->publicUrl();
    }

    public function loadConfig(): array
    {
        $path = $this->paths->fromPluginRoot('config/settings.json');
        return $path->decodedJson();
    }
}
```

## The Path Object

When you call `fromPluginRoot()`, you get a `Path` object with these methods:

### Getting URLs and Paths

```php
$path = $this->paths->fromPluginRoot('resources/style.css');

// Get the public URL (for use in HTML, enqueuing assets, etc.)
$url = $path->publicUrl(); // https://example.com/wp-content/plugins/my-plugin/resources/style.css

// Get the absolute file system path
$absolutePath = $path->absolutePath(); // /var/www/html/wp-content/plugins/my-plugin/resources/style.css
```

### Reading File Contents

```php
// Read file contents
$path = $this->paths->fromPluginRoot('data/config.json');
$content = $path->content(); // Returns file contents as string

// Check if file exists before reading
if ($path->exists()) {
    $content = $path->content();
}
```

### File Information

```php
$path = $this->paths->fromPluginRoot('assets/image.png');

// Check file properties
$exists = $path->exists();        // true/false
$readable = $path->isReadable();  // true/false
$writable = $path->isWritable();  // true/false

// Get file metadata
$size = $path->size();            // File size in bytes
$mimeType = $path->mimeType();    // e.g., "image/png"

// Decode JSON files
$data = $path->decodedJson();     // Returns decoded JSON as array
$obj = $path->decodedJson(false); // Returns decoded JSON as object
```

## Working with Different File Types

### Public Assets (CSS, JS, Images)

For files that need to be publicly accessible:

```php
class ThemeAssets
{
    public function __construct(
        private readonly Paths $paths
    ) {}

    public function enqueueStyles(): void
    {
        $stylePath = $this->paths->fromPluginRoot('public/css/main.css');
        
        if ($stylePath->exists()) {
            wp_enqueue_style(
                'my-plugin-styles',
                $stylePath->publicUrl(),
                [],
                filemtime($stylePath->absolutePath())
            );
        }
    }

    public function getLogoUrl(): string
    {
        return $this->paths->fromPluginRoot('public/images/logo.png')->publicUrl();
    }
}
```

### Private Files (Keys, Certificates, Config)

For files that should not be publicly accessible:

```php
use VAF\WP\Framework\Paths\PathException;

class SecurityManager
{
    public function __construct(
        private readonly Paths $paths
    ) {}

    public function loadPrivateKey(): string
    {
        $keyPath = $this->paths->fromPluginRoot('private/keys/api.key');
        
        if (!$keyPath->exists()) {
            throw new PathException('API key not found');
        }
        
        if (!$keyPath->isReadable()) {
            throw new PathException('API key is not readable');
        }
        
        return trim($keyPath->content());
    }
}
```

### Configuration Files

```php
class ConfigLoader
{
    public function __construct(
        private readonly Paths $paths
    ) {}

    public function loadDatabaseConfig(): array
    {
        $configPath = $this->paths->fromPluginRoot('config/database.php');
        
        if ($configPath->exists()) {
            return include $configPath->absolutePath();
        }
        
        return [];
    }

    public function loadJsonConfig(string $filename): array
    {
        $path = $this->paths->fromPluginRoot('config/' . $filename);
        
        if (!$path->exists()) {
            return [];
        }
        
        try {
            return $path->decodedJson();
        } catch (PathException $e) {
            // Handle invalid JSON
            return [];
        }
    }
}
```

## Error Handling

Path operations throw `PathException` when something goes wrong:

```php
use VAF\WP\Framework\Paths\PathException;

try {
    $path = $this->paths->fromPluginRoot('data/important.txt');
    $content = $path->content();
} catch (PathException $e) {
    // Handle file not found or read errors
    error_log('Failed to read file: ' . $e->getMessage());
}
```

## Best Practices

1. **Check file existence** before operations that might fail:
   ```php
   if ($path->exists() && $path->isReadable()) {
       $content = $path->content();
   }
   ```

2. **Use relative paths** from plugin root:
   ```php
   // Good
   $this->paths->fromPluginRoot('assets/style.css');
   
   // Avoid hardcoding absolute paths
   ```

3. **Separate public and private files** in your directory structure:
   ```
   my-plugin/
   ├── public/          # Files accessible via URL
   │   ├── css/
   │   ├── js/
   │   └── images/
   ├── private/         # Files not accessible via URL
   │   ├── keys/
   │   └── data/
   └── config/          # Configuration files
   ```

4. **Cache file contents** when appropriate:
   ```php
   class ConfigCache
   {
       private ?array $config = null;
       
       public function getConfig(): array
       {
           if ($this->config === null) {
               $path = $this->paths->fromPluginRoot('config/settings.json');
               $this->config = $path->decodedJson();
           }
           
           return $this->config;
       }
   }
   ```

## Integration with Other Framework Features

### Using with Templates

```php
class TemplateRenderer
{
    public function __construct(
        private readonly Paths $paths,
        private readonly TemplateRenderer $renderer
    ) {}

    public function renderTemplate(string $template, array $data = []): string
    {
        // Pass asset URLs to templates
        $data['assetUrl'] = $this->paths->fromPluginRoot('public/')->publicUrl();
        
        return $this->renderer->render($template, $data);
    }
}
```

### Using with Settings

```php
class FileBasedSetting extends Setting
{
    public function __construct(
        private readonly Paths $paths,
        string $baseName
    ) {
        parent::__construct('file_setting', $baseName);
    }

    protected function getDefaultConfigPath(): string
    {
        return $this->paths->fromPluginRoot('config/defaults.json')->absolutePath();
    }
}
```

## Example: Complete Asset Manager

Here's a complete example of an asset manager using the Paths system:

```php
namespace MyPlugin\Services;

use VAF\WP\Framework\Paths\Paths;
use VAF\WP\Framework\Hook\Attribute\AsHookContainer;
use VAF\WP\Framework\Hook\Attribute\Hook;

#[AsHookContainer]
class AssetManager
{
    private array $scripts = [];
    private array $styles = [];

    public function __construct(
        private readonly Paths $paths
    ) {}

    #[Hook(tag: "wp_enqueue_scripts")]
    public function enqueueAssets(): void
    {
        $this->enqueueStyles();
        $this->enqueueScripts();
    }

    private function enqueueStyles(): void
    {
        $styles = [
            'main' => 'public/css/main.css',
            'theme' => 'public/css/theme.css',
        ];

        foreach ($styles as $handle => $relativePath) {
            $path = $this->paths->fromPluginRoot($relativePath);
            
            if ($path->exists()) {
                wp_enqueue_style(
                    'my-plugin-' . $handle,
                    $path->publicUrl(),
                    [],
                    filemtime($path->absolutePath())
                );
            }
        }
    }

    private function enqueueScripts(): void
    {
        $scripts = [
            'app' => [
                'path' => 'public/js/app.js',
                'deps' => ['jquery'],
                'in_footer' => true
            ],
            'admin' => [
                'path' => 'public/js/admin.js',
                'deps' => ['jquery', 'wp-api'],
                'in_footer' => true
            ]
        ];

        foreach ($scripts as $handle => $config) {
            $path = $this->paths->fromPluginRoot($config['path']);
            
            if ($path->exists()) {
                wp_enqueue_script(
                    'my-plugin-' . $handle,
                    $path->publicUrl(),
                    $config['deps'],
                    filemtime($path->absolutePath()),
                    $config['in_footer']
                );
            }
        }
    }

    public function getImageUrl(string $imageName): string
    {
        return $this->paths->fromPluginRoot('public/images/' . $imageName)->publicUrl();
    }

    public function loadManifest(): array
    {
        $manifestPath = $this->paths->fromPluginRoot('public/build/manifest.json');
        
        if (!$manifestPath->exists()) {
            return [];
        }
        
        return $manifestPath->decodedJson();
    }
}
```
