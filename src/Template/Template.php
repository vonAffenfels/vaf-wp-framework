<?php

namespace VAF\WP\Framework\Template;

use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\TemplateRenderer\TemplateRenderer;

abstract class Template
{
    final public function __construct(
        private readonly BaseWordpress $base,
        private readonly TemplateRenderer $renderer,
        private readonly string $templateFile
    ) {
    }

    final public function render(): string
    {
        $jsData = $this->getJavascriptData();
        if ($jsData !== false) {
            $templateFileParts = explode('/', $this->templateFile);
            $this->addScriptData($this->base->getName() . '_' . end($templateFileParts), $jsData);
        }
        return $this->renderer->render($this->templateFile, $this->getContextData());
    }

    final public function output(): void
    {
        echo $this->render();
    }

    final public function addScript(string $src, array $deps = [], array $adminAjaxActions = []): self
    {
        $handle = $this->base->getName() . '_' . pathinfo($src, PATHINFO_FILENAME);
        $src = $this->base->getAssetUrl($src);
        wp_enqueue_script($handle, $src, $deps, false, true);

        foreach ($adminAjaxActions as $ajaxAction) {
            $this->registerAdminAjaxAction($ajaxAction);
        }

        return $this;
    }

    final public function registerAdminAjaxAction(string $action): self
    {
        $completeActionName = $this->base->getName() . '_' . $action;
        $this->addScriptData(str_replace('-', '_', $completeActionName), [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'data' => [
                '_ajax_nonce' => wp_create_nonce($action),
                'action' => $completeActionName
            ]
        ]);

        return $this;
    }

    private function addScriptData(string $var, array $data): void
    {
        // Make sure that we have the common JS included to hook onto that handle
        wp_enqueue_script('common');
        wp_localize_script('common', $var, $data);
    }

    protected function getJavascriptData(): false|array
    {
        return false;
    }

    abstract protected function getContextData(): array;
}
