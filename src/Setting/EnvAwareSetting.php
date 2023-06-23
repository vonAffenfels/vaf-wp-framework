<?php

namespace VAF\WP\Framework\Setting;

use VAF\WP\Framework\BaseWordpress;

abstract class EnvAwareSetting extends Setting
{
    private mixed $envValue;

    public function __construct(string $name, BaseWordpress $base, mixed $default = null)
    {
        parent::__construct($name, $base, $default);
        $this->envValue = $this->parseEnv();
    }

    protected function get(?string $key = null)
    {
        if (is_null($this->envValue)) {
            return parent::get($key);
        }

        return is_null($key) ? $this->envValue : $this->envValue[$key];
    }

    abstract protected function parseEnv();
}
