<?php

namespace VAF\WP\Framework\TemplateRenderer\Engine;

abstract class TemplateEngine
{
    private bool $debug = false;

    public function enableDebug(): void
    {
        $this->debug = true;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    abstract public function render(string $file, array $context): string;
}
