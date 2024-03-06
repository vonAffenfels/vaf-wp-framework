<?php

namespace VAF\WP\Framework\QueryBuilder;

use LogicException;
use VAF\WP\Framework\PostObjects\PostObject;
use VAF\WP\Framework\PostObjects\PostObjectList;
use WP_Query;

/**
 * __Post & Page Parameters__
 * p => filterPosts (will result in the use of post__in even if specified only one post)
 * name =>
 * page_id => filterPosts (will result in the use of post__in)
 * pagename =>
 * post_parent =>
 * post_parent__in =>
 * post_parent__not_in =>
 * post__in => filterPosts (can't be used at the same time as post__not_in)
 * post__not_in => excludePosts (can't be used at the same time as post__in)
 * post_name__in =>
 *
 * __Order Parameters__
 *
 * __Status Parameters__
 * post_status => filterPostStatus
 *
 * __Post Type Parameters__
 * post_type => filterPostTypes
 *
 * __Pagination Parameters__
 * posts_per_page => setLimit (if you want unlimited posts use setUnlimited)
 */

class Builder
{
    /*******************
     * Order Parameters
     *******************/

    private ?array $order = null;

    public function orderBy(string|OrderFields $field, OrderDirection $direction = OrderDirection::DESC): static
    {
        if ($field instanceof OrderFields) {
            $field = $field->value;
        }

        // @TODO: Check if field is present if meta field (or move it to the buildQueryArgs() function

        return $this;
    }

    public function orderByPostFilter(OrderDirection $direction = OrderDirection::DESC): static
    {
        return $this->orderBy(OrderFields::POST_FILTER, $direction);
    }

    /*************************
     * Post / Page Parameters
     *************************/

    private ?array $posts = null;

    private ?array $excludePosts = null;

    public function filterPosts(PostObject|int|array $posts, bool $append = false): static
    {
        if (!empty($this->excludePosts)) {
            throw new LogicException("Can't mix filterPosts() and excludePosts() in same query!");
        }

        if (!is_array($posts)) {
            $posts = [$posts];
        }

        $posts = array_map(function (int|PostObject $post): int {
            return is_int($post) ? $post : $post->getId();
        }, $posts);

        if ($append) {
            $this->posts = array_merge($this->posts ?? [], $posts);
        } else {
            $this->posts = $posts;
        }

        return $this;
    }

    public function excludePosts(PostObject|int|array $posts, bool $append = false): static
    {
        if (!empty($this->posts)) {
            throw new LogicException("Can't mix filterPosts() and excludePosts() in same query!");
        }

        if (!is_array($posts)) {
            $posts = [$posts];
        }

        $posts = array_map(function (int|PostObject $post): int {
            return is_int($post) ? $post : $post->getId();
        }, $posts);

        if ($append) {
            $this->excludePosts = array_merge($this->excludePosts ?? [], $posts);
        } else {
            $this->excludePosts = $posts;
        }

        return $this;
    }

    /********************
     * Status Parameters
     ********************/

    private ?array $postStatus = null;

    public function filterPostStatus(string|PostStatus|array $postStatus, bool $append = false): static
    {
        if (!is_array($postStatus)) {
            $postStatus = [$postStatus];
        }

        $postStatus = array_map(function (string|PostStatus $status): string {
            return is_string($status) ? $status : $status->value;
        }, $postStatus);

        if ($append) {
            $this->postStatus = array_merge($this->postStatus ?? [], $postStatus);
        } else {
            $this->postStatus = $postStatus;
        }

        return $this;
    }

    /***********************
     * Post Type Parameters
     ***********************/

    private ?array $postTypes = null;

    public function filterPostType(string|array $postType, bool $append = false): static
    {
        if (!is_array($postType)) {
            $postType = [$postType];
        }

        if ($append) {
            $this->postTypes = array_merge($this->postTypes ?? [], $postType);
        } else {
            $this->postTypes = $postType;
        }

        return $this;
    }

    /************************
     * Pagination Parameters
     ************************/

    private ?int $limit = null;

    public function setUnlimited(): static
    {
        return $this->setLimit(-1);
    }

    public function setLimit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /******************
     * Query Execution
     ******************/

    private function buildQueryArgs(): array
    {
        $args = [];

        // Pagination Parameters
        if (!is_null($this->limit)) {
            $args['posts_per_page'] = $this->limit;
        }

        // Post Type Parameters
        if (!empty($this->postTypes ?? [])) {
            if (count($this->postTypes) === 1) {
                $args['post_type'] = reset($this->postTypes);
            } else {
                $args['post_type'] = $this->postTypes;
            }
        }

        // Status Parameters
        if (!empty($this->postStatus ?? [])) {
            if (count($this->postStatus) === 1) {
                $args['post_status'] = reset($this->postStatus);
            } else {
                $args['post_status'] = $this->postStatus;
            }
        }

        // Post / Page Parameter
        if (!empty($this->posts ?? [])) {
            $args['post__in'] = $this->posts;
        }

        if (!empty($this->excludePosts ?? [])) {
            $args['post__not_in'] = $this->excludePosts;
        }

        return $args;
    }

    public function getQuery(): WP_Query
    {
        return new WP_Query($this->buildQueryArgs());
    }

    public function execute(): PostObjectList
    {
        return PostObjectList::getByWPQuery($this->getQuery());
    }

    public function getFirstPost(): ?PostObject
    {
        $list = $this->execute();
        return $list->count() > 0 ? $list->first() : null;
    }
}
