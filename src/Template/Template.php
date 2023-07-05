<?php

namespace VAF\WP\Framework\Template;

abstract class Template
{
    private array $data = [];

    final public function __construct(
        private readonly TemplateRenderer $renderer,
        private readonly string $templateFile
    ) {
    }

    final public function render(): string
    {
        return $this->renderer->render($this->templateFile, $this->data);
    }

    final public function output(): void
    {
        echo $this->render();
    }

    final protected function setData(string $key, $value): void
    {
        $this->data[$key] = $value;
    }
}
