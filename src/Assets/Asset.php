<?php

namespace VAF\WP\Framework\Assets;

use VAF\WP\Framework\Plugin;

class Asset
{
    public static function fromVaV1($name, array $assetDefinition, array $dependencies): self
    {
        $asset = new static(
            $name,
            $assetDefinition['path'] ?? '',
            $assetDefinition['hash'] ?? '',
            $dependencies
        );


        return $asset;
    }

    /**
     * @param string $name
     * @param string $path
     * @param string $hash
     * @param AssetDependencies[] $dependencies
     */
    private function __construct(
        public readonly string $name,
        public readonly string $path,
        public readonly string $hash,
        public readonly array $dependencies,
    ) {
    }

    public function enqueueAsScript(string $handle, Plugin $plugin): void
    {
        wp_enqueue_script(
            $handle,
            $plugin->getUrl() . $this->path,
            $this->dependencies,
            $this->hash
        );
    }

    public function enqueueAsStyle(string $handle, Plugin $plugin): void
    {
        wp_enqueue_style(
            $handle,
            $plugin->getUrl() . $this->path,
            deps: ['wp-components'],
            ver: $this->hash
        );
    }

    public function withExtraDependency(string $extraDependency): self
    {
        return new Asset(
            $this->name,
            $this->path,
            $this->hash,
            [...$this->dependencies, $extraDependency],
        );
    }
}
