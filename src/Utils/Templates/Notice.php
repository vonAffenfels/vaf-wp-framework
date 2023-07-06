<?php

namespace VAF\WP\Framework\Utils\Templates;

use VAF\WP\Framework\Template\Attribute\AsTemplate;
use VAF\WP\Framework\Template\Template;
use VAF\WP\Framework\Utils\NoticeType;

#[AsTemplate(templateFile: '@vaf-wp-framework/utils/notice')]
final class Notice extends Template
{
    public function setContent(string $content): self
    {
        $this->setData('content', $content);
        return $this;
    }

    public function setIsDismissible(bool $value): self
    {
        $this->setData('isDismissible', $value);
        return $this;
    }

    public function setType(NoticeType $type): self
    {
        $this->setData('type', $type);
        return $this;
    }
}
