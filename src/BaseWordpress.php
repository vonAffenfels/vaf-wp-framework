<?php

namespace VAF\WP\Library;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use VAF\WP\Library\Kernel\Kernel;

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
        $this->kernel->boot();
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
}
