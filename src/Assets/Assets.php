<?php

namespace VAF\WP\Framework\Assets;

class Assets
{

    private array $assets;

    public static function fromVaV1InDirectory($directory): self
    {
        $assets = new static();

        $assetDefinitions = [];
        foreach (json_decode(file_get_contents("$directory/webpack-assets.json"), true)['files'] ?? [] as $name => $definition) {
            $assetDefinitions[$name] = Asset::fromVaV1(
                $name,
                $definition,
                AssetDependencies::forFile("$directory/$name")->dependencies
            );
        }
        $assets->assets = $assetDefinitions;


        return $assets;
    }

    public function asset($name): Asset
    {
        return $this->assets[$name];
    }
}
