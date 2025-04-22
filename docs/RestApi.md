# RestApi

wordpress supports creating your own rest api routes under `/wp-json/`. The vaf-wp-framework lets you create your rest
routes by adding a `AsRestContainer` Attribute to your class, then adding `RestRoute` Attributes to public functions.

## Example

```php
use VAF\WP\Framework\RestAPI\Attribute\AsRestContainer;
use VAF\WP\Framework\RestAPI\Attribute\RestRoute;

#[AsRestContainer]
class ExampleWithRestApi {
    public function 
    
    
    #[RestRoute('route-url/sub-path')]
    public function restRoute() {
        return [
            'json' => 'result'
        ];
    }
}
```

## Accessing The rest api routes

Use the `RestApiLink` class to create links to your rest api routes. This class will automatically generate the correct
url for the given `AsRestContainer` class and url given in `RestRoute`

```php

use VAF\WP\Framework\RestAPI\Attribute\AsRestContainer;
use VAF\WP\Framework\RestAPI\Attribute\RestRoute;
use VAF\WP\Framework\Hook\Attribute\AsHookContainer;
use VAF\WP\Framework\Hook\Attribute\Hook;
use VAF\WP\Framework\RestAPI\RestApiLink;

#[AsRestContainer]
#[AsHookContainer]
class ExampleWithRestApi {
    public function __construct(private readonly Plugin $plugin) {
    }
    
    #[Hook('wp_enqueue_scripts')]
    public function injectIntoJs() {
        wp_enqueue_script('script-id', ...);
        wp_add_inline_script(
            'script-id',
            'const my_script_urls = '.json_encode([
                'do_stuff' => \VAF\WP\Framework\RestAPI\RestApiLink::forContainerPluginRoute(self::class, $this->plugin, 'route-url/sub-path')
                    ->publicUrl()
            ]),
            'before'
        );
    }
    
    #[RestRoute('route-url/sub-path')]
    public function doStuff() {
    }
}
```
