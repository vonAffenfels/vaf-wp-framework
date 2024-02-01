<?php

namespace VAF\WP\Framework\PostObjects;

use VAF\WP\Framework\PostObjects\Attributes\PostType;

#[PostType('post')]
class Post extends PostObject
{
    public function getId(): int
    {
        return $this->getPost()->ID;
    }

    public function getPostType(): string
    {
        return $this->getPost()->post_type;
    }

    public function getPermalink(): string
    {
        return get_permalink($this->getPost());
    }
}
