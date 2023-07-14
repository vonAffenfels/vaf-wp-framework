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
        private readonly Request $request,
        private readonly Template $template
    ) {
    }

    final public function handle(): void
    {
        $page = $this->request->getParam($this->pageVar, Request::TYPE_GET, $this->defaultSlug);

        $this->template->setPageTitle($this->pageTitle);
        $this->template->output();
    }
}
