<?php

namespace VAF\WP\Framework\Utils\Templates\Admin;

use VAF\WP\Framework\Template\Attribute\IsTemplate;
use VAF\WP\Framework\Template\Template;

#[IsTemplate(templateFile: '@vaf-wp-framework/admin/tabbedPage')]
final class TabbedPage extends Template
{
    private string $pageTitle = '';
    private array $tabs = [];

    public function setPageTitle(string $title): self
    {
        $this->pageTitle = $title;
        return $this;
    }

    public function setTabs(array $tabs): self
    {
        $this->tabs = $tabs;
        return $this;
    }

    protected function getContextData(): array
    {
        return [
            'pageTitle' => $this->pageTitle,
            'tabs' => $this->tabs
        ];
    }
}
