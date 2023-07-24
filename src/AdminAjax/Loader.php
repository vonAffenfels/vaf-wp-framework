<?php

namespace VAF\WP\Framework\AdminAjax;

use Exception;
use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\Kernel\WordpressKernel;
use VAF\WP\Framework\Request;
use VAF\WP\Framework\System\Parameters\Parameter;
use VAF\WP\Framework\System\Parameters\ParameterBag;
use VAF\WP\Framework\Utils\Capabilities;
use VAF\WP\Framework\Utils\HttpResponseCodes;

final class Loader
{
    public function __construct(
        private readonly WordpressKernel $kernel,
        private readonly BaseWordpress $base,
        private readonly array $adminAjaxContainer,
        private readonly Request $request
    ) {
    }

    public function registerAdminAjaxActions(): void
    {
        foreach ($this->adminAjaxContainer as $serviceId => $adminAjaxContainer) {
            foreach ($adminAjaxContainer as $action) {
                $parameterBag = ParameterBag::fromArray($action['params']);
                $actionName = $this->base->getName() . '_' . $action['action'];
                $method = $action['callback'];

                /** @var Capabilities $capability */
                $capability = $action['capability'];

                $callback = function () use ($action, $parameterBag, $capability, $method, $serviceId) {
                    check_ajax_referer($action['action']);

                    if (!is_null($capability) && !current_user_can($capability->value)) {
                        wp_send_json_error(
                            [
                                'code' => HttpResponseCodes::HTTP_FORBIDDEN->value,
                                'message' => 'Can\'t run action - forbidden'
                            ],
                            HttpResponseCodes::HTTP_FORBIDDEN->value
                        );
                        wp_die();
                    }

                    $params = [];

                    /** @var Parameter $parameter */
                    foreach ($parameterBag->getParams() as $parameter) {
                        if ($parameter->isServiceParam()) {
                            $params[$parameter->getName()] = $this->kernel->getContainer()->get($parameter->getType());
                            continue;
                        }

                        $name = $parameter->getName();
                        if (
                            !$this->request->hasParam($name, Request::TYPE_POST)
                            && $this->request->hasParam($parameter->getNameLower(), Request::TYPE_POST)
                        ) {
                            $name = $parameter->getNameLower();
                        }

                        if (
                            !$parameter->isOptional()
                            && !$this->request->hasParam($name, Request::TYPE_POST)
                        ) {
                            wp_send_json_error(
                                [
                                    'code' => HttpResponseCodes::HTTP_BAD_REQUEST->value,
                                    'message' => 'Missing parameter ' . $name . '!'
                                ],
                                HttpResponseCodes::HTTP_BAD_REQUEST->value
                            );
                            wp_die();
                        }

                        $value = $this->request->getParam(
                            $name,
                            Request::TYPE_POST,
                            $parameter->getDefault()
                        );

                        # Handle type
                        switch ($parameter->getType()) {
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
                        $params[$parameter->getName()] = $value;
                    }

                    $container = $this->kernel->getContainer()->get($serviceId);
                    try {
                        /** @var Response $retVal */
                        $retVal = $container->$method(...$params);
                        wp_send_json($retVal->toArray());
                    } catch (Exception $e) {
                        $retVal = Response::error($e::class . ': ' . $e->getMessage());
                        wp_send_json(
                            $retVal->toArray(),
                            HttpResponseCodes::HTTP_INTERNAL_SERVER_ERROR->value
                        );
                    }

                    wp_die();
                };

                if (is_null($capability)) {
                    add_action('wp_ajax_nopriv_' . $actionName, $callback);
                }

                add_action('wp_ajax_' . $actionName, $callback);
            }
        }
    }
}
