<?php

namespace VAF\WP\Framework\TemplateRenderer\Engine;

use VAF\WP\Framework\TemplateRenderer\Attribute\AsTemplateEngine;

#[AsTemplateEngine(extension: 'phtml')]
final class PHTMLEngine extends TemplateEngine
{
    private array $data = [];

    public function render(string $file, array $context): string
    {
        $this->data = $context;

        unset($context);
        ob_start();
        include($file);
        return ob_get_clean();
    }

    public function getData(string $name, $default = null)
    {
        if (!isset($this->data[$name])) {
            return $default;
        }

        return $this->data[$name];
    }

    public function __get(string $name)
    {
        return $this->getData($name);
    }

    public function __isset(string $name)
    {
        return isset($this->data[$name]);
    }
}
