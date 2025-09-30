# Elementor Widgets with Dependency Injection

The VAF WordPress Framework provides a clean way to create Elementor widgets with automatic dependency injection support.

## Overview

The Elementor module allows you to create widgets that extend Elementor's functionality while having access to services from the Symfony dependency injection container. This is achieved through:

- A `#[AsElementorWidget]` attribute to mark widget classes
- A `ContainerAwareWidget` base class that extends `Elementor\Widget_Base`
- Automatic dependency injection via an `injectDependencies()` method
- Integration with the framework's service container

## Basic Usage

### 1. Create a Widget Class

```php
<?php

namespace YourPlugin\Elementor;

use VAF\WP\Framework\Elementor\Attribute\AsElementorWidget;
use VAF\WP\Framework\Elementor\ContainerAwareWidget;
use YourPlugin\Services\ProductService;
use YourPlugin\Services\CartService;

#[AsElementorWidget]
class ProductWidget extends ContainerAwareWidget
{
    private ProductService $productService;
    private CartService $cartService;

    protected function injectDependencies(
        ProductService $productService,
        CartService $cartService
    ): void {
        $this->productService = $productService;
        $this->cartService = $cartService;
    }

    public function get_name()
    {
        return 'your_product_widget';
    }

    public function get_title()
    {
        return 'Product Widget';
    }

    public function get_icon()
    {
        return 'eicon-products';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Content',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label' => 'Product ID',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $productId = $settings['product_id'];

        if (empty($productId)) {
            echo '<p>Please select a product.</p>';
            return;
        }

        $product = $this->productService->getProduct($productId);
        $cartUrl = $this->cartService->getCartUrl();

        if (!$product) {
            echo '<p>Product not found.</p>';
            return;
        }

        // Render your product widget
        echo "<div class='product-widget'>";
        echo "<h3>{$product->getName()}</h3>";
        echo "<p>{$product->getDescription()}</p>";
        echo "<a href='{$cartUrl}?add={$productId}' class='add-to-cart'>Add to Cart</a>";
        echo "</div>";
    }
}
```

### 2. Register Services (if needed)

Ensure your services are registered in your plugin's service configuration:

```yaml
# config/services.yaml
services:
  YourPlugin\Services\ProductService:
    autowire: true

  YourPlugin\Services\CartService:
    autowire: true
```

### 3. That's It!

The widget will be automatically:
- Discovered by the framework
- Registered with Elementor
- Injected with dependencies before being used

## How It Works

1. **Discovery**: The `LoaderCompilerPass` scans for classes with `#[AsElementorWidget]` attribute
2. **Analysis**: It analyzes the `injectDependencies()` method to determine required services
3. **Container Setup**: Required services are marked as public in the container
4. **Registration**: During WordPress `init`, the container is passed to widget classes
5. **Injection**: When Elementor instantiates widgets, dependencies are automatically injected

## Advanced Usage

### Conditional Dependencies

You can make dependencies optional by using nullable types:

```php
protected function injectDependencies(
    ProductService $productService,
    ?OptionalService $optionalService = null
): void {
    $this->productService = $productService;
    $this->optionalService = $optionalService;
}
```

### Complex Widgets

For more complex widgets, you can use any Elementor features as normal:

```php
#[AsElementorWidget]
class AdvancedProductWidget extends ContainerAwareWidget
{
    private ProductService $productService;
    private TemplateRenderer $templateRenderer;

    protected function injectDependencies(
        ProductService $productService,
        TemplateRenderer $templateRenderer
    ): void {
        $this->productService = $productService;
        $this->templateRenderer = $templateRenderer;
    }

    protected function register_controls()
    {
        // Multiple sections with various controls
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Content',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'products',
            [
                'label' => 'Products',
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->productService->getProductOptions(),
            ]
        );

        $this->end_controls_section();

        // Style controls
        $this->start_controls_section(
            'style_section',
            [
                'label' => 'Style',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => 'Text Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .product-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        echo $this->templateRenderer->render('elementor/product-grid', [
            'products' => $this->productService->getProducts($settings['products']),
            'settings' => $settings,
        ]);
    }
}
```

## Requirements

- PHP 8.1+
- Elementor plugin must be installed and active
- Services must be properly registered in the Symfony container

## Troubleshooting

### Widget Not Appearing
- Ensure the class has the `#[AsElementorWidget]` attribute
- Check that the class extends `ContainerAwareWidget`
- Verify Elementor is installed and active

### Dependency Injection Not Working
- Make sure services are registered in the container
- Check that parameter types in `injectDependencies()` match service class names
- Ensure the method is marked as `protected`

### Services Not Found
- Verify service registration in `config/services.yaml`
- Check that services are autowired correctly
- Make sure service classes exist and are autoloaded