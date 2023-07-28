<?php

namespace VAF\WP\Framework;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use VAF\WP\Framework\Kernel\Kernel;

abstract class BaseWordpress
{
    protected Kernel $kernel;

    /**
     * @throws Exception
     */
    protected function __construct(
        private readonly string $name,
        private readonly string $path,
        private readonly string $url,
        private readonly bool $debug = false
    ) {
        $this->kernel = $this->createKernel();
    }

    /**
     * @throws Exception
     */
    final public static function buildContainer(): void
    {
        $obj = new static('__BUILD__', getcwd(), '__BUILD__', true);
        $obj->getContainer();
    }

    public function configureContainer(ContainerBuilder $builder): void
    {
    }

    abstract protected function createKernel(): Kernel;

    final public function getDebug(): bool
    {
        return $this->debug;
    }

    final public function getPath(): string
    {
        return $this->path;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    final public function getUrl(): string
    {
        return $this->url;
    }

    final public function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }

    public function getAssetUrl(string $asset): string
    {
        return $this->url . 'public/' . $asset;
    }
}
