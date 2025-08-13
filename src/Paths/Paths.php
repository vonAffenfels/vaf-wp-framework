<?php

namespace VAF\WP\Framework\Paths;

class Paths
{
    public function __construct(
        private readonly string $basePath,
        private readonly string $baseUrl
    ) {
    }

    public function fromPluginRoot(string $relativePath): Path
    {
        $relativePath = ltrim($relativePath, '/\\');

        $absolutePath = rtrim($this->basePath, '/\\') . DIRECTORY_SEPARATOR . $relativePath;
        $absolutePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);

        $publicUrl = rtrim($this->baseUrl, '/') . '/' . str_replace('\\', '/', $relativePath);

        return new Path($absolutePath, $publicUrl);
    }
}
