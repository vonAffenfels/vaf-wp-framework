<?php

namespace VAF\WP\Framework\Paths;

class Path
{
    public function __construct(
        private readonly string $absolutePath,
        private readonly string $publicUrl
    ) {
    }

    public function decodedJson(bool $associative = true, int $depth = 512, int $flags = 0): mixed
    {
        $content = $this->content();

        $decoded = json_decode($content, $associative, $depth, $flags);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PathException(
                "Failed to decode JSON from {$this->absolutePath}: " . json_last_error_msg()
            );
        }

        return $decoded;
    }

    public function publicUrl(): string
    {
        return $this->publicUrl;
    }

    public function content(): string
    {
        if (!file_exists($this->absolutePath)) {
            throw new PathException("File not found: {$this->absolutePath}");
        }

        $content = file_get_contents($this->absolutePath);
        if ($content === false) {
            throw new PathException("Failed to read file: {$this->absolutePath}");
        }

        return $content;
    }

    public function exists(): bool
    {
        return file_exists($this->absolutePath);
    }

    public function absolutePath(): string
    {
        return $this->absolutePath;
    }

    public function isReadable(): bool
    {
        return is_readable($this->absolutePath);
    }

    public function isWritable(): bool
    {
        return is_writable($this->absolutePath);
    }

    public function size(): int
    {
        if (!$this->exists()) {
            throw new PathException("File not found: {$this->absolutePath}");
        }

        $size = filesize($this->absolutePath);
        if ($size === false) {
            throw new PathException("Failed to get file size: {$this->absolutePath}");
        }

        return $size;
    }

    public function mimeType(): string
    {
        if (!$this->exists()) {
            throw new PathException("File not found: {$this->absolutePath}");
        }

        $mimeType = mime_content_type($this->absolutePath);
        if ($mimeType === false) {
            throw new PathException("Failed to get mime type: {$this->absolutePath}");
        }

        return $mimeType;
    }
}
