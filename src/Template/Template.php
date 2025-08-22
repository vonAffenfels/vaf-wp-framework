<?php

namespace VAF\WP\Framework\Template;

use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\TemplateRenderer\TemplateRenderer;

abstract class Template
{
    public function __construct(
        private readonly BaseWordpress $base,
        private readonly TemplateRenderer $renderer,
        private readonly string $templateFile
    ) {
    }

    public function render(): string
    {
        $jsData = $this->getJavascriptData();
        if ($jsData !== false) {
            $templateFileParts = explode('/', $this->templateFile);
            $name = str_replace(
                '-',
                '_',
                $this->base->getName() . '_' . end($templateFileParts)
            );
            $this->addScriptData($name, $jsData);
        }
        return $this->renderer->render($this->templateFile, $this->getContextData());
    }

    public function output(): void
    {
        echo $this->render();
    }

    public function addScript(string $src, array $deps = [], array $adminAjaxActions = []): self
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

        $code = sprintf(
            '(window.vaf_admin_ajax = window.vaf_admin_ajax || {})[\'%1$s\'] = %2$s;',
            $completeActionName,
            json_encode([
                'ajaxurl' => admin_url('admin-ajax.php'),
                'data' => [
                    '_ajax_nonce' => wp_create_nonce($action),
                    'action' => $completeActionName
                ]
            ])
        );
        wp_enqueue_script('common');
        wp_add_inline_script('common', $code, 'before');
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
