<?php

namespace VAF\WP\Framework\PostObjects\PostTypes;

use VAF\WP\Framework\PostObjects\Attributes\PostType;
use VAF\WP\Framework\PostObjects\PostObject;

#[PostType(self::TYPE_NAME)]
class Post extends PostObject
{
    public const TYPE_NAME = 'post';

    public function getPermalink(): string
    {
        return get_permalink($this->getPost());
    }

    public function getTitle(): string
    {
        return get_the_title($this->getPost());
    }
}
