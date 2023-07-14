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
        $name = $this->base->getName();

        foreach ($this->adminAjaxContainer as $serviceId => $adminAjaxContainer) {
            foreach ($adminAjaxContainer as $action) {
                $parameterBag = ParameterBag::fromArray($action['params']);
                $actionName = $this->base->getName() . '_' . $action['action'];
                $method = $action['callback'];

                /** @var Capabilities $capability */
                $capability = $action['capability'];

                $callback = function () use ($parameterBag, $capability, $method, $serviceId) {
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

                        if (
                            !$parameter->isOptional()
                            && !$this->request->hasParam($parameter->getNameLower(), Request::TYPE_POST)
                        ) {
                            wp_send_json_error(
                                [
                                    'code' => HttpResponseCodes::HTTP_BAD_REQUEST->value,
                                    'message' => 'Missing parameter ' . $parameter->getNameLower() . '!'
                                ],
                                HttpResponseCodes::HTTP_BAD_REQUEST->value
                            );
                            wp_die();
                        }

                        $value = $this->request->getParam(
                            $parameter->getNameLower(),
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
                        $retVal = $container->$method(...$params);

                        if ($retVal !== false) {
                            wp_send_json_success($retVal === true ? null : $retVal);
                        } else {
                            wp_send_json_error();
                        }
                    } catch (Exception $e) {
                        wp_send_json_error(
                            [
                                'code' => $e->getCode(),
                                'message' => $e->getMessage()
                            ],
                            HttpResponseCodes::HTTP_INTERNAL_SERVER_ERROR->value
                        );
                    }

                    wp_die();
                };

                if (is_null($capability)) {
                    add_action('wp_ajax_nopriv_' . $actionName, $callback);
                } else {
                    add_action('wp_ajax_' . $actionName, $callback);
                }
            }
        }
    }
}