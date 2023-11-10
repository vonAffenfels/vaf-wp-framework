<?php

namespace VAF\WP\Framework\TemplateRenderer\Engine;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TwigFunction;
use VAF\WP\Framework\TemplateRenderer\Attribute\AsTemplateEngine;
use VAF\WP\Framework\TemplateRenderer\Engine\Twig\Extension;
use VAF\WP\Framework\TemplateRenderer\Engine\Twig\FileLoader;

#[AsTemplateEngine(extension: 'twig')]
final class TwigEngine extends TemplateEngine
{
    private Environment $twig;

    public function __construct(
        FileLoader $loader,
        Extension $extension
    ) {
        $this->twig = new Environment($loader);
        $this->twig->addExtension($extension);
        $this->twig->addFunction(new TwigFunction('__', fn (...$parameters) => __(...$parameters)));
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
