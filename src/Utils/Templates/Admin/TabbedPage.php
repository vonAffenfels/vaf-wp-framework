<?php

namespace VAF\WP\Framework\Utils\Templates\Admin;

use VAF\WP\Framework\Template\Attribute\IsTemplate;
use VAF\WP\Framework\Template\Template;

#[IsTemplate(templateFile: '@vaf-wp-framework/admin/tabbedPage')]
final class TabbedPage extends Template
{
    private string $pageTitle = '';

    public function setPageTitle(string $title): self
    {
        $this->pageTitle = $title;
        return $this;
    }

    protected function getContextData(): array
    {
        return [
            'pageTitle' => $this->pageTitle
        ];
    }
}
