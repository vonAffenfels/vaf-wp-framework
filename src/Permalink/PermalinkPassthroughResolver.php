<?php

namespace VAF\WP\Framework\Permalink;

class PermalinkPassthroughResolver extends PermalinkResolver
{
    public function permalinkForPostId(string $postId): string
    {
        return "permalink_for_${postId}";
    }
}
