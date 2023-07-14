<?php

namespace VAF\WP\Framework\AdminAjax;

use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\System\Parameters\ParameterBag;

final class Loader
{
    public function __construct(
        private readonly BaseWordpress $base,
        private readonly array $adminAjaxContainer
    ) {
    }

    public function registerAdminAjaxActions(): void
    {
        $name = $this->base->getName();

        foreach ($this->adminAjaxContainer as $serviceId => $adminAjaxContainer) {
            foreach ($adminAjaxContainer as $action) {
                $parameterBag = ParameterBag::fromArray($action['params']);
                $actionName = $this->base->getName() . '_' . $action['action'];
                $capability = $action['capability'];

                $callback = function () use ($parameterBag, $capability) {
                    var_dump($parameterBag, $capability);
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
