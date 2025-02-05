<?php

namespace VAF\WP\Framework\RestAPI;

use Exception;
use Throwable;
use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\Kernel\WordpressKernel;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

final class Loader
{
    public function __construct(
        private readonly WordpressKernel $kernel,
        private readonly BaseWordpress   $base,
        private readonly array           $restContainer
    )
    {
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
                    'permission_callback' => match ($restRoute['permission']['type']) {
                        'none' => fn() => true,
                        'wordpress_permission' => fn() => current_user_can($restRoute['permission']['wordpress_permission_name']),
                    },
                    'methods' => $restRoute['method']->value,
                    'callback' => function (WP_REST_Request $request) use (
                        $serviceId,
                        $methodName,
                        $params,
                        $restRoute
                    ): array|WP_HTTP_Response {
                        $suppressOutput = SuppressOutput::enabled($restRoute['suppressEchoOutput'] ?? true);
                        $suppressOutput->start();

                        $return = [
                            'success' => false
                        ];

                        foreach ($restRoute['params'] as $name) {
                            if (!isset($restRoute['paramsDefault'][$name]) && !$request->has_param($name)) {
                                throw new Exception(
                                    sprintf(
                                        'Parameter "%s" for RestRoute "%s"%s not provided and without default value.',
                                        $restRoute['paramsLower'][$name],
                                        $restRoute['uri'],
                                        (!empty($restRoute['namespace'] ?? '')) ?
                                            sprintf(' of namespace "%s"', $restRoute['namespace']) :
                                            ''
                                    )
                                );
                            }

                            if ($request->has_param($name)) {
                                $value = $request->get_param($name);
                            } else {
                                $value = $restRoute['paramsDefault'][$name];
                            }

                            # Handle type
                            switch ($restRoute['paramTypes'][$name]) {
                                case 'int':
                                    $value = (int)$value;
                                    break;

                                case 'bool':
                                    $value = in_array(strtolower($value), ['1', 'on', 'true']);
                                    break;

                                case 'string':
                                default:
                                    # Nothing to do as $value is already a string
                                    break;
                            }
                            $params[$restRoute['paramsLower'][$name]] = $value;
                        }

                        try {
                            $container = $this->kernel->getContainer()->get($serviceId);
                            $retVal = $container->$methodName(...$params);

                            if ($retVal instanceof WP_HTTP_Response) {
                                return $retVal;
                            }

                            if (!($restRoute['wrapResponse'] ?? true)) {
                                return new WP_REST_Response($retVal);
                            }

                            if ($retVal !== false) {
                                $return['success'] = true;

                                if (!is_null($retVal)) {
                                    $return['data'] = $retVal;
                                }
                            }
                        } catch (Throwable $e) {
                            if (!($restRoute['wrapResponse'] ?? true)) {
                                $suppressOutput->finish();
                                return new WP_REST_Response(
                                    [
                                        'message' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString(),
                                    ],
                                    500
                                );
                            }

                            $return['success'] = false;
                            $return['message'] = $e->getMessage();
                        }

                        $suppressOutput->finish();
                        return $return;
                    }
                ];

                register_rest_route($namespace, '/' . $restRoute['uri'], $options);
            }
        }
    }
}
