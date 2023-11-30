# Dynamic Blocks

Dynamic Blocks can be registered by creating a class and giving it the AsDynamicBlock PHP Attribute. The class must have
a render method with the following signature

    public function render(array $blockAttributes, string $content): string

## Full Example

```php
<?php

#[AsDynamicBlock('example-block')
class ExampleBlock
{
    public function render(array $blockAttributes, string $content): string
    {
        return 'Hello World';
    }
}
```

## block attributes as class

The blockAttributes can also be passed as class type. To do so you must implement a class with the static function

    public static function fromBlockAttributes(array $blockAttributes): self

and typehint the `$blockAttributes` parameter with this class.

## Full Example

```php
<?php

class ExampleBlockAttributes
{
    public readonly string $title;

    public static function fromBlockAttributes(array $blockAttributes): self
    {
        $attributes = new self();
        
        $attributes->title = $blockAttributes['title'] ?? '';

        return $attributes;
    }
}

#[AsDynamicBlock('example-block')
class ExampleBlock
{
    public function render(ExampleBlockAttributes $blockAttributes, string $content): string
    {
        return "<h1>{$blockAttributes->title}</h1> Hello World";
    }
}
```
