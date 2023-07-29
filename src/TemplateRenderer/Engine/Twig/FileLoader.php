<?php

namespace VAF\WP\Framework\TemplateRenderer\Engine\Twig;

use Twig\Loader\LoaderInterface;
use Twig\Source;
use VAF\WP\Framework\TemplateRenderer\NamespaceHandler;

class FileLoader implements LoaderInterface
{
    public function __construct(
        private readonly NamespaceHandler $handler
    ) {
    }

    private function getFile(string $name): string|false
    {
        if (is_file($name)) {
            return $name;
        }

        $file = $this->handler->searchTemplateFile($name, ['twig'], $foundExtension);
        return $file ?: false;
    }

    public function getSourceContext(string $name): Source
    {
        $file = $this->getFile($name);
        if (empty($file)) {
            return new Source('', $name, '');
        }

        return new Source(file_get_contents($file), $name, $file);
    }

    public function getCacheKey(string $name): string
    {
        $file = $this->getFile($name);
        if (empty($file)) {
            return '';
        }

        return $file;
    }

    public function exists(string $name): bool
    {
        $file = $this->getFile($name);
        if (empty($file)) {
            return false;
        }

        return true;
    }

    public function isFresh(string $name, int $time): bool
    {
        $file = $this->getFile($name);
        if (empty($file)) {
            return false;
        }

        return filemtime($file) < $time;
    }
}
