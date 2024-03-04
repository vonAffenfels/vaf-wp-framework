<?php

namespace VAF\WP\Framework\TemplateRenderer;

use InvalidArgumentException;
use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\Plugin;
use VAF\WP\Framework\TemplateRenderer\Engine\TemplateEngine;

final class TemplateRenderer
{
    private const NAMESPACE = '@vaf-wp-framework';

    private bool $debug = false;

    public function __construct(
        private readonly BaseWordpress $base,
        private readonly NamespaceHandler $handler,
        private readonly GlobalContext $globalContext,
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

    public function enableDebug(): void
    {
        $this->debug = true;
    }

    public function registerNamespace(string $namespace, array $directories, bool $overwrite = false): void
    {
        $this->handler->registerNamespace($namespace, $directories, $overwrite);
    }

    public function render(string $template, array $context = []): string
    {
        $extension = '';
        $templateFile = $this->handler->searchTemplateFile($template, array_keys($this->engines), $extension);
        if ($templateFile === false) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not find the template "%s"! Searched in directories: [%s]',
                    $template,
                    implode(', ', $this->handler->getSearchDirectoriesForTemplate($template))
                )
            );
        }

        // Add global context
        $context['global'] = $this->globalContext;

        /** @var TemplateEngine $engineObj */
        $engineObj = $this->base->getContainer()->get($this->engines[$extension]);
        if ($this->debug) {
            $engineObj->enableDebug();
        }

        return $engineObj->render($templateFile, $context);
    }

    public function output(string $template, array $context = []): void
    {
        echo $this->render($template, $context);
    }
}
