<?php

namespace VAF\WP\Framework\TemplateRenderer\Engine;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use VAF\WP\Framework\TemplateRenderer\Attribute\AsTemplateEngine;
use VAF\WP\Framework\TemplateRenderer\Engine\Twig\FileLoader;

#[AsTemplateEngine(extension: 'twig')]
final class TwigEngine extends TemplateEngine
{
    private Environment $twig;

    public function __construct(
        private readonly FileLoader $loader
    ) {
        $this->twig = new Environment($this->loader);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(string $file, array $context): string
    {
        return $this->twig->render($file, $context);
    }
}
