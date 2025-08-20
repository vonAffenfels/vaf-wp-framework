<?php

namespace VAF\WP\Framework\Setting;

abstract class EnvAwareSetting extends Setting
{
    private mixed $envValue;

    public function __construct(string $name, string $baseName, mixed $default = null)
    {
        parent::__construct($name, $baseName, $default);
        $this->envValue = $this->parseEnv();
    }

    protected function get(?string $key = null)
    {
        if ($this->isFromEnv($key)) {
            return is_null($key) ? $this->envValue : ($this->envValue[$key] ?? $this->default[$key]);
        }

        return parent::get($key);
    }

    final public function isFromEnv(?string $key = null): bool
    {
        if (is_null($this->envValue)) {
            return false;
        }

        return is_null($key) || !is_null($this->envValue[$key] ?? null);
    }

    abstract protected function parseEnv();
}
