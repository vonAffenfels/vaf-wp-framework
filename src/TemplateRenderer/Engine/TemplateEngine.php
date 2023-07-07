<?php

namespace VAF\WP\Framework\TemplateRenderer\Engine;

abstract class TemplateEngine
{
    abstract public function render(string $file, array $context): string;
}
