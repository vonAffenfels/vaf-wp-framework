<?php

namespace VAF\WP\Framework\TemplateRenderer\Functions\BuiltIn;

use VAF\WP\Framework\TemplateRenderer\Attribute\AsFunctionContainer;
use VAF\WP\Framework\TemplateRenderer\Attribute\IsFunction;

#[AsFunctionContainer]
class Wordpress
{
    #[IsFunction('wp_head')]
    public function wpHead(): void
    {
        wp_head();
    }

    #[IsFunction('get_bloginfo', safeHTML: true)]
    public function getBlogInfo(...$parameter): string
    {
        return get_bloginfo(...$parameter);
    }

    #[IsFunction('wp_editor')]
    public function wpEditor(...$parameter): void
    {
        wp_editor(...$parameter);
    }

    #[IsFunction('wp_nonce_field')]
    public function wpNonceField(...$parameter): string
    {
        return wp_nonce_field(...$parameter);
    }

    #[IsFunction('__')]
    public function __(...$parameter): string
    {
        return __(...$parameter);
    }

    #[IsFunction('do_shortcode')]
    public function doShortcode(...$parameter): void
    {
        do_shortcode(...$parameter);
    }

    #[IsFunction('wp_footer')]
    public function wpFooter(): void
    {
        wp_footer();
    }
}
