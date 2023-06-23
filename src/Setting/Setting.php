<?php

namespace VAF\WP\Framework\Setting;

use VAF\WP\Framework\BaseWordpress;

abstract class Setting
{
    private bool $loaded = false;

    private mixed $value = null;

    public function __construct(
        private readonly string $name,
        private readonly BaseWordpress $base,
        private readonly mixed $default = null
    ) {
    }

    private function getOptionName(): string
    {
        return $this->base->getName() . '_' . $this->name;
    }

    protected function get(?string $key = null)
    {
        if (!$this->loaded) {
            $this->value = get_option($this->getOptionName(), $this->default);
            $this->loaded = true;
        }

        return is_null($key) ? $this->value : ($this->value[$key] ?? $this->default);
    }

    final public function __invoke()
    {
        return $this->get();
    }
}
