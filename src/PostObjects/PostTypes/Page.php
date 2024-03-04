<?php

namespace VAF\WP\Framework\PostObjects\PostTypes;

use VAF\WP\Framework\PostObjects\Attributes\PostType;

#[PostType(self::TYPE_NAME)]
class Page extends Post
{
    public const TYPE_NAME = 'page';

    public function getParent(): ?Page
    {
        $postParent = $this->getPost()->post_parent;
        $return = null;

        if ($postParent > 0) {
            $return = Page::getById($postParent);
        }

        return $return;
    }
}
