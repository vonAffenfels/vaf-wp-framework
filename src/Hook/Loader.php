<?php

namespace VAF\WP\Library\Hook;

use VAF\WP\Library\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(private readonly WordpressKernel $kernel, private readonly array $hookContainer)
    {
    }

    public function registerHooks(): void
    {
        foreach ($this->hookContainer as $serviceId => $hookContainer) {
            foreach ($hookContainer as $hook => $data) {
                add_filter($hook, function (...$args) use ($serviceId, $data) {
                    $params = [];

                    for ($i = 0; $i <= array_key_last($data['serviceParams']); $i++) {
                        if (isset($data['serviceParams'][$i])) {
                            $params[] = $this->kernel->getContainer()->get($data['serviceParams'][$i]);
                        } else {
                            $params[] = array_shift($args);
                        }
                    }

                    $params = array_merge($params, $args);

                    $hookContainer = $this->kernel->getContainer()->get($serviceId);
                    $methodName = $data['method'];
                    return $hookContainer->$methodName(...$params);
                }, $data['priority'], $data['numParams']);
            }
        }
    }
}
