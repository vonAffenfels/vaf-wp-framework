<?php

namespace VAF\WP\Framework\RestAPI;

use Closure;
use ReflectionClass;
use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\RestAPI\Attribute\AsRestContainer;

class RestApiLink
{
    private string $route;
    private string $namespace;
    private Closure $askWordpress;

    public static function forContainerPluginRoute(string $container, BaseWordpress $wordpress, string $route): self
    {
        $reflection = new ReflectionClass($container);
        $attributes = $reflection->getAttributes(AsRestContainer::class);
        if (empty($attributes)) {
            throw new \LogicException("RestApiLink requested for non rest api class");
        }

        return self::forNamespacePluginRoute($attributes[0]->newInstance()->namespace, $wordpress, $route);
    }

    public static function forNamespacePluginRoute(string $namespace, BaseWordpress $wordpress, string $route): self
    {
        $restApiLink = new static();

        $restApiLink->namespace = $wordpress->getName() . match (true) {
                empty($namespace) => '',
                !empty($namespace) && str_starts_with($namespace, '/') => $namespace,
                !empty($namespace) && !str_starts_with($namespace, '/') => '/' . $namespace,
        };
        $restApiLink->route = str_starts_with($route, '/') ? $route : ('/' . $route);

        return $restApiLink;
    }

    private function __construct()
    {
        $this->askWordpress = fn($url) => rest_url($this->namespace() . $this->uri());
    }

    public function withFakeWordpressCall(Closure $fakeWordpressCall): self
    {
        $clone = clone $this;

        $clone->askWordpress = $fakeWordpressCall;

        return $clone;
    }

    public function namespace(): string
    {
        return $this->namespace;
    }

    public function uri(): string
    {
        return $this->route;
    }

    public function publicUrl(): string
    {
        return ($this->askWordpress)($this->namespace() . $this->uri());
    }
}
