<?php

namespace VAF\WP\Framework\Permalink;

class PermalinkResolver
{

    public function permalinkForPostId(string $postId): string
    {
        return get_permalink($postId);
    }

}
