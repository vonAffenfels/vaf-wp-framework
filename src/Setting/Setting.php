<?php

namespace VAF\WP\Framework\Setting;

use VAF\WP\Framework\Wordpress\Wordpress;

/**
 * @template T
 */
abstract class Setting
{
    private bool $loaded = false;

    private mixed $value = null;

    private bool $dirty = false;

    private static array $fakes = [];

    public function __construct(
        private readonly string $name,
        private readonly string $baseName,
        protected readonly mixed $default = null
    ) {
    }

    private function getOptionName(): string
    {
        return $this->baseName . '_' . $this->name;
    }

    protected function get(?string $key = null)
    {
        // Check if this setting is faked
        if (isset(self::$fakes[$this->name]) && !$this->loaded) {
            // Treat the fake value as a database value and apply fromDb conversion
            $this->value = ($this->conversion()->fromDb)(self::$fakes[$this->name]);
            $this->loaded = true;
        }

        $this->migrateIfNecessary();

        if (!$this->loaded) {
            $this->value = ($this->conversion()->fromDb)(Wordpress::get_option($this->getOptionName(), $this->default));

            $this->loaded = true;
        }

        return is_null($key) ? $this->value : ($this->value[$key] ?? $this->default[$key]);
    }

    protected function set($value, ?string $key = null, bool $doSave = true): self
    {
        // If faked, load the fake value first with conversion
        if (isset(self::$fakes[$this->name]) && !$this->loaded) {
            $this->value = ($this->conversion()->fromDb)(self::$fakes[$this->name]);
            $this->loaded = true;
        }

        $convertedValue = fn() => $this->conversion()->fromInput !== null
            ? ($this->conversion()->fromInput)($value)
            : $value;

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
        // If this setting is faked, update the fake value with toDb conversion
        if (isset(self::$fakes[$this->name])) {
            self::$fakes[$this->name] = ($this->conversion()->toDb)($this->value);
            $this->dirty = false;
            return;
        }

        if ($this->dirty) {
            Wordpress::update_option($this->getOptionName(), ($this->conversion()->toDb)($this->value));
            $this->dirty = false;
        }
    }

    /**
     * @param ...$args
     * @return mixed|Setting|null|T
     */
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
        if (
            $this->loaded
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

    protected function conversion(): Conversion
    {
        return Conversion::identity();
    }

    /**
     * Fake a setting value for testing purposes.
     * The fake will be used instead of calling WordPress functions.
     * The value should be in database format (as it would be stored in WordPress options table).
     * It will be converted using fromDb when retrieved and toDb when saved.
     *
     * @param string $settingName The setting name (without prefix)
     * @param mixed $value The fake value in database format
     */
    public static function fakeSetting(string $settingName, mixed $value): void
    {
        self::$fakes[$settingName] = $value;
    }

    /**
     * Clear all fake settings.
     */
    public static function clearFakes(): void
    {
        self::$fakes = [];
    }

    /**
     * Check if a setting is currently faked.
     *
     * @param string $settingName The setting name (without prefix)
     * @return bool
     */
    public static function isFaked(string $settingName): bool
    {
        return isset(self::$fakes[$settingName]);
    }
}
