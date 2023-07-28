<?php

namespace VAF\WP\Framework\TemplateRenderer;

use InvalidArgumentException;
use VAF\WP\Framework\TemplateRenderer\Engine\TemplateEngine;
use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\Plugin;

final class TemplateRenderer
{
    private const NAMESPACE = '@vaf-wp-framework';

    private array $namespaces = [];

    public function __construct(
        private readonly BaseWordpress $base,
        private readonly NamespaceHandler $handler,
        private readonly array $engines
    ) {
        $namespacePaths = [];
        $themeSuffixDir = 'templates/';
        if ($this->base instanceof Plugin) {
            $namespacePaths[] = $this->base->getPath() . 'templates/';
            $themeSuffixDir .= $this->base->getName();
        }

        $baseThemeDirectory = trailingslashit(get_template_directory());
        $childThemeDirectory = trailingslashit(get_stylesheet_directory());

        // Add parent theme template directory to the top of the list
        array_unshift($namespacePaths, trailingslashit($baseThemeDirectory . $themeSuffixDir));

        // If we have a child theme then we will add its template directory at the top most position
        if ($baseThemeDirectory !== $childThemeDirectory) {
            array_unshift($namespacePaths, trailingslashit($childThemeDirectory . $themeSuffixDir));
        }

        $this->registerNamespace($this->base->getName(), $namespacePaths);

        $this->registerNamespace(self::NAMESPACE, [
            trailingslashit(realpath(trailingslashit(dirname(__FILE__)) . '../../templates/'))
        ]);
    }

    public function registerNamespace(string $namespace, array $directories): void
    {
        $this->handler->registerNamespace($namespace, $directories);
    }

    public function render(string $template, array $context = []): string
    {
        foreach ($this->engines as $extension => $engine) {
            $templateFile = $this->handler->getTemplateFile($template . '.' . $extension);
            if ($templateFile !== false) {
                /** @var TemplateEngine $engineObj */
                $engineObj = $this->base->getContainer()->get($engine);
                return $engineObj->render($templateFile, $context);
            }
        }

        throw new InvalidArgumentException(
            sprintf(
                'Could not find the template "%s"! Searched in directories: [%s]',
                $template,
                implode(', ', $this->handler->getSearchDirectoriesForTemplate($template))
            )
        );
    }

    public function output(string $template, array $context = []): void
    {
        echo $this->render($template, $context);
    }
}
