<?php

namespace VAF\WP\Framework\Elementor;

use VAF\WP\Framework\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(
        private readonly WordpressKernel $kernel,
        private readonly array $widgetContainer
    ) {
    }

    public function registerWidgets(): void
    {
        // Pass container to each widget class during 'init' hook
        add_action('init', function () {
            foreach ($this->widgetContainer as $serviceId => $widgetData) {
                $widgetClass = $widgetData['class'];
                $widgetClass::setContainer($this->kernel->getContainer());
            }
        });

        // Register widgets with Elementor
        add_action('elementor/widgets/register', function ($widgetsManager) {
            foreach ($this->widgetContainer as $serviceId => $widgetData) {
                $widgetClass = $widgetData['class'];
                $widgetInstance = new $widgetClass();
                $widgetsManager->register($widgetInstance);
            }
        });
    }
}
