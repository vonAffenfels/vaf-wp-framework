<?php

namespace VAF\WP\Framework\Assets;

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
}
