<?php

namespace VAF\WP\Framework\RestAPI;

use Exception;
use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\Kernel\WordpressKernel;
use WP_REST_Request;

final class Loader
{
    public function __construct(
        private readonly WordpressKernel $kernel,
        private readonly BaseWordpress $base,
        private readonly array $restContainer
    ) {
    }

    public function registerRestRoutes(): void
    {
        $name = $this->base->getName();

        foreach ($this->restContainer as $serviceId => $restContainer) {
            foreach ($restContainer as $restRoute) {
                $namespace = $name;
                if (!empty($restRoute['namespace'])) {
                    $namespace .= '/' . $restRoute['namespace'];
                }

                $params = [];
                foreach ($restRoute['serviceParams'] as $param => $service) {
                    $params[$param] = $this->kernel->getContainer()->get($service);
                }

                $methodName = $restRoute['callback'];

                $options = [
                    'methods' => $restRoute['method']->value,
                    'callback' => function (WP_REST_Request $request) use ($serviceId, $methodName, $params): array {
                        $return = [
                            'success' => false
                        ];

                        try {
                            $container = $this->kernel->getContainer()->get($serviceId);
                            $retVal = $container->$methodName(...$params);

                            if ($retVal !== false) {
                                $return['success'] = true;

                                if (!is_null($retVal)) {
                                    $return['data'] = $retVal;
                                }
                            }
                        } catch (Exception $e) {
                            $return['success'] = false;
                            $return['message'] = $e->getMessage();
                        }

                        return $return;
                    }
                ];

                register_rest_route($namespace, '/' . $restRoute['uri'], $options);
            }
        }
    }
}
