<?php

namespace VAF\WP\Framework\Utils\Templates\Admin;

use VAF\WP\Framework\Template\Attribute\IsTemplate;
use VAF\WP\Framework\Template\Template;
use VAF\WP\Framework\Utils\NoticeType;

#[IsTemplate(templateFile: '@vaf-wp-framework/admin/notice')]
final class Notice extends Template
{
    private string $content = '';
    private bool $isDismissible = true;
    private NoticeType $type = NoticeType::INFO;

    protected function getContextData(): array
    {
        return [
            'content' => $this->content,
            'isDismissible' => $this->isDismissible,
            'type' => $this->type
        ];
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setIsDismissible(bool $value): self
    {
        $this->isDismissible = $value;
        return $this;
    }

    public function setType(NoticeType $type): self
    {
        $this->type = $type;
        return $this;
    }
}
