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

    public function __construct(private readonly BaseWordpress $base, private readonly array $engines)
    {
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

        $this->namespaces['@' . $this->base->getName()] = $namespacePaths;

        // Register framework namespace
        $this->namespaces[self::NAMESPACE] = [
            trailingslashit(realpath(trailingslashit(dirname(__FILE__)) . '../../templates/'))
        ];
    }
    public function render(string $template, array $context = []): string
    {
        // Check if we have a namespace and template
        $templateParts = explode('/', $template);
        if (count($templateParts) < 2) {
            throw new InvalidArgumentException(
                'Template name should contain namespace and template file. Provided: "' . $template . '"'
            );
        }

        // Extract namespace from provided template name
        $namespace = array_shift($templateParts);
        if (!isset($this->namespaces[$namespace])) {
            throw new InvalidArgumentException(sprintf('Template namespace "%s" is not registered!', $namespace));
        }
        $template = implode('/', $templateParts);

        $engine = '';
        $templateFile = '';
        $found = false;

        // Search for a fitting template in the namespace directories
        foreach ($this->namespaces[$namespace] as $namespaceDirectory) {
            /** @var string $extension */
            foreach ($this->engines as $extension => $engine) {
                $templateFile = trailingslashit($namespaceDirectory) . $template . '.' . $extension;
                if (file_exists($templateFile) && is_readable($templateFile)) {
                    $found = true;
                    break 2;
                }
            }
        }

        if (!$found) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not find the template "%s"! Searched in directories: [%s]',
                    $template,
                    implode(', ', $this->namespaces[$namespace])
                )
            );
        }

        /** @var TemplateEngine $engineObj */
        $engineObj = $this->base->getContainer()->get($engine);
        return $engineObj->render($templateFile, $context);
    }

    public function output(string $template, array $context = []): void
    {
        echo $this->render($template, $context);
    }
}
