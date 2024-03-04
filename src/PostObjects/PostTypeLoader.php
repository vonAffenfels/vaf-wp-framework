<?php

namespace VAF\WP\Framework\PostObjects;

use LogicException;
use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\PostObjects\PostTypes\NavMenuItem;
use VAF\WP\Framework\PostObjects\PostTypes\Page;
use VAF\WP\Framework\PostObjects\PostTypes\Post;

class PostTypeLoader
{
    private static array $internalPostObjects = [
        'post' => Post::class,
        'page' => Page::class,
        'nav_menu_item' => NavMenuItem::class
    ];

    public function __construct(private readonly BaseWordpress $base, private readonly array $postTypes)
    {
    }

    /**
     * @throws LogicException
     */
    public function registerPostTypes(): void
    {
        foreach ($this->postTypes as $postType => $postObjectClass) {
            if ((self::$internalPostObjects[$postType] ?? '') !== $postObjectClass) {
                throw new LogicException("Post type $postType is internal!");
            }

            add_filter(
                self::getHookName($postType),
                function (?PostObject $obj) use ($postObjectClass): PostObject {
                    /** @var PostObject $obj */
                    $obj = $this->base->getContainer()->get($postObjectClass);
                    return $obj;
                }
            );
        }
    }

    private static function getHookName(string $postType): string
    {
        return 'vaf_wp_framework/post_type/' . $postType . '/get_object';
    }

    public static function getObjectForPostType(string $postType): PostObject
    {
        /** @var ?PostObject $obj */
        $obj = null;

        $hookName = self::getHookName($postType);
        if (has_filter($hookName)) {
            $obj = apply_filters($hookName, null);
        }

        if (is_null($obj)) {
            $obj = self::getObjectForPostType(Post::TYPE_NAME);
        }

        return $obj;
    }
}
