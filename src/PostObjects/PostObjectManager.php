<?php

namespace VAF\WP\Framework\PostObjects;

use VAF\WP\Framework\BaseWordpress;
use WP_Post;
use WP_Query;

class PostObjectManager
{
    private array $internalPostObjects = [
        'post' => Post::class,
        'page' => Page::class,
        'nav_menu_item' => NavMenuItem::class
    ];

    public function __construct(
        array $registeredPostObjects,
        private readonly BaseWordpress $base
    ) {
        foreach ($registeredPostObjects as $postType => $postObjectClass) {
            add_filter(
                $this->getHookName($postType),
                function (?PostObject $obj) use ($postObjectClass): PostObject {
                    /** @var PostObject $obj */
                    $obj = $this->base->getContainer()->get($postObjectClass);
                    return $obj;
                }
            );
        }
    }

    private function getHookName(string $postType): string
    {
        return 'vaf_wp_framework/post_type/' . $postType . '/get_object';
    }

    private function getObjectForPostType(string $postType): PostObject
    {
        if (isset($this->internalPostObjects[$postType])) {
            /** @var PostObject $obj */
            $obj = $this->base->getContainer()->get($this->internalPostObjects[$postType]);
            return $obj;
        }

        /** @var ?PostObject $obj */
        $obj = null;

        $hookName = $this->getHookName($postType);
        if (has_filter($hookName)) {
            $obj = apply_filters($hookName, null);
        }

        if (is_null($obj)) {
            $obj = $this->base->getContainer()->get(Post::class);
        }

        return $obj;
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

    public function getByPostArray(array $posts): PostObjectList
    {
        return new PostObjectList($this, $posts);
    }

    public function getQueriedObject(): ?PostObject
    {
        $obj = get_queried_object();
        $ret = null;

        if ($obj instanceof WP_Post) {
            $ret = $this->getByWPPost($obj);
        }

        return $ret;
    }
}
