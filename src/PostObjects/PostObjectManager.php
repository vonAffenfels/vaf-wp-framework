<?php

namespace VAF\WP\Framework\PostObjects;

use VAF\WP\Framework\BaseWordpress;
use WP_Post;
use WP_Query;

class PostObjectManager
{
    public const HOOK_GET_POST_OBJECT_BY_POST_TYPE = 'vaf-wp-framework/get-post-object-by-post-type';

    public function __construct(
        private readonly array $internalPostObjects,
        private readonly BaseWordpress $base
    ) {
    }

    private function getObjectForPostType(string $postType): PostObject
    {
        // Handle container internal post types
        if (isset($this->internalPostObjects[$postType])) {
            /** @var PostObject $obj */
            $obj = $this->base->getContainer()->get($this->internalPostObjects[$postType]);
            return $obj;
        }

        /** @var ?PostObject $obj */
        $obj = apply_filters(self::HOOK_GET_POST_OBJECT_BY_POST_TYPE, null, $postType);
        if (is_null($obj)) {
            // If no object was provided by any plugin simply return Post
            $obj = $this->base->getContainer()->get(Post::class);
        }

        return $obj;
    }

    public function getInternalClassForPostType(string $postType): ?string
    {
        return $this->internalPostObjects[$postType] ?? null;
    }

    public function getByWPPost(WP_Post $post): PostObject
    {
        $obj = $this->getObjectForPostType($post->post_type);
        $obj->setPost($post);

        return $obj;
    }

    public function getById(int $postId): PostObject|false
    {
        $post = WP_Post::get_instance($postId);
        if (false === $post) {
            return false;
        }

        return $this->getByWPPost($post);
    }

    public function getByWPQuery(WP_Query $query): PostObjectList
    {
        return new PostObjectList($this, $query->posts);
    }
}
