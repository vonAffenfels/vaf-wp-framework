<?php

namespace VAF\WP\Framework\Setting;

use VAF\WP\Framework\BaseWordpress;

abstract class Setting
{
    private bool $loaded = false;

    private mixed $value = null;

    public function __construct(private readonly BaseWordpress $base)
    {
    }

    private function getOptionName(): string
    {
        return $this->base->getName() . '_' . $this->getSettingName();
    }

    protected function get(?string $key = null)
    {
        if (!$this->loaded) {
            $this->value = get_option($this->getOptionName(), null);
            $this->loaded = true;
        }

        return is_null($key) ? $this->value : $this->value[$key];
    }

    final public function __invoke()
    {
        return $this->get();
    }

    abstract protected function getSettingName(): string;
}
