<?php

namespace VAF\WP\Framework\TemplateRenderer;

class GlobalContext
{
    private array $data = [];

    public function add(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!isset($this->data[$key])) {
            return $default;
        }

        return $this->data[$key];
    }

    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    public function __isset(string $key)
    {
        return isset($this->data[$key]);
    }
}
