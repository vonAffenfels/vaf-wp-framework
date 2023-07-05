<?php

namespace VAF\WP\Framework\Template\Engine;

abstract class TemplateEngine
{
    abstract public function render(string $file, array $context): string;
}
