<?php

namespace VAF\WP\Framework\AdminPages;

use VAF\WP\Framework\Request;
use VAF\WP\Framework\Utils\Templates\Admin\TabbedPage as Template;

abstract class TabbedPage
{
    final public function __construct(
        private readonly string $pageTitle,
        private readonly string $pageVar,
        private readonly ?string $defaultSlug,
        private readonly array $handler,
        private readonly Request $request,
        private readonly Template $template
    ) {
    }

    private function buildUrl(string $slug): string
    {
        return add_query_arg([
            $this->pageVar => $slug
        ], admin_url('admin.php'));
    }

    final public function handle(): void
    {
        $firstTab = reset($this->handler);
        $page = $this->request->getParam($this->pageVar, Request::TYPE_GET, $this->defaultSlug ?? $firstTab['slug']);

        $tabs = [];
        foreach ($this->handler as $slug => $tab) {
            $tabs[] = [
                'active' => $tab['slug'] === $page,
                'title' => $tab['title'],
                'url' => $this->buildUrl($tab['slug'])
            ];
        }

        $this->template->setTabs($tabs);
        $this->template->setPageTitle($this->pageTitle);
        $this->template->output();
    }
}
