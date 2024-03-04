<?php

namespace VAF\WP\Framework\PostObjects;

use Countable;
use Iterator;
use WP_Post;
use WP_Query;
use VAF\WP\Framework\PostObjects\PostTypes\Post;

class PostObjectList implements Iterator, Countable
{
    public static function getByWPQuery(WP_Query $query): self
    {
        return self::getByPostArray($query->posts);
    }

    public static function getByPostArray(array $posts): PostObjectList
    {
        $obj = new self();
        $obj->setPosts($posts);
        return $obj;
    }

    private array $posts = [];

    private function getPostObject(WP_Post|int|PostObject $post): PostObject
    {
        if ($post instanceof WP_Post) {
            return Post::getByWPPost($post);
        } elseif (is_int($post)) {
            return Post::getById($post);
        } else {
            return $post;
        }
    }

    public function setPosts(array $posts): self
    {
        $this->posts = array_map(function (WP_Post|int $post): PostObject {
            return $this->getPostObject($post);
        }, array_filter($posts, function ($post): bool {
            return $post instanceof WP_Post || is_int($post);
        }));

        return $this;
    }

    public function addPost(PostObject|WP_Post|int $post): self
    {
        $this->posts[] = $this->getPostObject($post);

        return $this;
    }

    public function current(): PostObject
    {
        return current($this->posts);
    }

    public function next(): void
    {
        next($this->posts);
    }

    public function key(): ?int
    {
        return key($this->posts);
    }

    public function valid(): bool
    {
        return $this->key() !== null;
    }

    public function rewind(): void
    {
        reset($this->posts);
    }

    public function get(string $name): mixed
    {
        return $this->current()->get($name);
    }

    public function first(): ?PostObject
    {
        return $this->posts[0] ?? null;
    }

    public function sort(callable $sortFunction): self
    {
        usort($this->posts, $sortFunction);
        return $this;
    }

    public function filter(callable $filterFunction): self
    {
        $this->posts = array_filter($this->posts, $filterFunction);
        return $this;
    }

    public function count(): int
    {
        return count($this->posts);
    }
}
