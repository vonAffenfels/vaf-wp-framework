<?php

namespace VAF\WP\Framework\TemplateRenderer\Functions\BuiltIn;

use VAF\WP\Framework\TemplateRenderer\Attribute\AsFunctionContainer;
use VAF\WP\Framework\TemplateRenderer\Attribute\IsFunction;

#[AsFunctionContainer]
class Wordpress
{
    #[IsFunction('wp_editor')]
    public function wpEditor(string $content, string $editorId, array $settings = []): void
    {
        wp_editor($content, $editorId, $settings);
    }

    #[IsFunction('wp_nonce_field')]
    public function wpNonceField(
        int|string $action = -1,
        string $name = '_wpnonce',
        bool $referer = true,
        bool $display = true
    ): string {
        return wp_nonce_field($action, $name, $referer, $display);
    }
}
