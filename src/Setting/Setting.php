<?php

namespace VAF\WP\Framework\Setting;

use VAF\WP\Framework\BaseWordpress;

abstract class Setting
{
    private bool $loaded = false;

    private mixed $value = null;

    private bool $dirty = false;

    public function __construct(
        private readonly string        $name,
        private readonly BaseWordpress $base,
        protected readonly mixed       $default = null
    )
    {
    }

    private function getOptionName(): string
    {
        return $this->base->getName() . '_' . $this->name;
    }

    protected function get(?string $key = null)
    {
        $this->migrateIfNecessary();

        if (!$this->loaded) {
            $this->value = ($this->conversion()?->fromDb)( get_option($this->getOptionName(), $this->default) )
                ?? get_option($this->getOptionName(), $this->default);

            $this->loaded = true;
        }

        return is_null($key) ? $this->value : ($this->value[$key] ?? $this->default[$key]);
    }

    protected function set($value, ?string $key = null, bool $doSave = true): self
    {
        $convertedValue = fn() => ($this->conversion()?->fromInput)($value) ?? $value;

        if (is_null($key)) {
            $this->value = $convertedValue();
        } else {
            if (!is_array($this->value)) {
                $this->value = [$convertedValue()];
            }
            $this->value[$key] = $convertedValue();
        }

        $this->dirty = true;

        if ($doSave) {
            $this->save();
        }

        return $this;
    }

    final public function save(): void
    {
        if ($this->dirty) {
            update_option($this->getOptionName(), ($this->conversion?->toDb)($this->value) ?? $this->value);
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

    private function migrateIfNecessary(?string $key = null): void
    {
        if ($this->loaded
            || $this->migration() === null
            || ($this->migration()->migrated)($key)
        ) {
            return;
        }

        $value = ($this->migration()->value)();
        $this->set($value, $key);
        ($this->migration()->clear)();
        $this->loaded = true;
    }

    protected function migration(): ?Migration
    {
        return null;
    }

    protected function conversion(): ?Conversion
    {
        return null;
    }
}
