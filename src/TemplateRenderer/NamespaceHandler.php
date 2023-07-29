<?php

namespace VAF\WP\Framework\TemplateRenderer;

use InvalidArgumentException;

class NamespaceHandler
{
    private array $namespaces = [];

    public function getSearchDirectories(string $namespace): array
    {
        return $this->namespaces[$namespace] ?? [];
    }

    public function getNamespaces(): array
    {
        return array_keys($this->namespaces);
    }

    public function getSearchDirectoriesForTemplate(string $template): array
    {
        $namespace = $this->getNamespaceFromTemplate($template);
        if (!$namespace) {
            throw new InvalidArgumentException(
                'Template name should contain namespace and template file. Provided: "' . $template . '"'
            );
        }

        return $this->getSearchDirectories($namespace);
    }

    private function getNamespaceFromTemplate(string $template): false|string
    {
        $templateParts = explode('/', $template);
        if (count($templateParts) < 2) {
            return false;
        }

        return $templateParts[0];
    }

    private function getTemplateFileFromTemplate(string $template): false|string
    {
        $templateParts = explode('/', $template);
        if (count($templateParts) < 2) {
            return false;
        }

        array_shift($templateParts);
        return implode('/', $templateParts);
    }

    public function searchTemplateFile(
        string $template,
        array $availableExtensions,
        string &$foundExtension
    ): false|string {
        $namespace = $this->getNamespaceFromTemplate($template);
        if (!$namespace) {
            throw new InvalidArgumentException(
                'Template name should contain namespace and template file. Provided: "' . $template . '"'
            );
        }

        if (!isset($this->namespaces[$namespace])) {
            throw new InvalidArgumentException(sprintf('Template namespace "%s" is not registered!', $namespace));
        }

        $template = $this->getTemplateFileFromTemplate($template);

        foreach ($this->namespaces[$namespace] as $namespaceDirectory) {
            foreach ($availableExtensions as $extension) {
                $templateFile = trailingslashit($namespaceDirectory) . $template . '.' . $extension;
                if (file_exists($templateFile) && is_readable($templateFile)) {
                    $foundExtension = $extension;
                    return $templateFile;
                }
            }
        }

        return false;
    }

    public function registerNamespace(string $namespace, array $paths): void
    {
        if (!str_starts_with($namespace, '@')) {
            $namespace = '@' . $namespace;
        }

        if (isset($this->namespaces[$namespace])) {
            throw new InvalidArgumentException(
                sprintf('Namespace %s is already registered!', $namespace)
            );
        }

        $this->namespaces[$namespace] = $paths;
    }
}
