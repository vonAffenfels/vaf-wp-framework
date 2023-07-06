<?php

namespace VAF\WP\Framework\Setting;

use VAF\WP\Framework\BaseWordpress;

abstract class Setting
{
    private bool $loaded = false;

    private mixed $value = null;

    private bool $dirty = false;

    public function __construct(
        private readonly string $name,
        private readonly BaseWordpress $base,
        protected readonly mixed $default = null
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

    protected function set($value, ?string $key = null, bool $doSave = true): self
    {
        if (is_null($key)) {
            $this->value = $value;
        } else {
            if (!is_array($this->value)) {
                $this->value = [$value];
            }
            $this->value[$key] = $value;
        }

        $this->dirty = true;

        if ($doSave) {
            $this->save();
        }

        return $this;
    }

    private function save(): void
    {
        if ($this->dirty) {
            update_option($this->getOptionName(), $this->value);
            $this->dirty = false;
        }
    }

    final public function __invoke(...$args)
    {
        if (count($args) === 1) {
            // Provided parameter
            // So save the setting
            return $this->set($args[0]);
        } else {
            return $this->get();
        }
    }
}
