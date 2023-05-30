<?php

namespace VAF\WP\Library\Setting;

use VAF\WP\Library\BaseWordpress;

abstract class EnvAwareSetting extends Setting
{
    private mixed $envValue;

    public function __construct(BaseWordpress $base)
    {
        parent::__construct($base);
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
