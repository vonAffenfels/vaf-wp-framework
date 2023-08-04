<?php

namespace VAF\WP\Framework\PostObjects;

use Iterator;
use ReturnTypeWillChange;
use WP_Post;

class PostObjectList extends PostObject implements Iterator
{
    private array $posts;

    public function __construct(PostObjectManager $manager, array $posts)
    {
        $this->posts = array_map(function (WP_Post|int $post) use ($manager): PostObject {
            if ($post instanceof WP_Post) {
                return $manager->getByWPPost($post);
            } else {
                return $manager->getById($post);
            }
        }, array_filter($posts, function ($post): bool {
            return $post instanceof WP_Post || is_int($post);
        }));
    }

    #[ReturnTypeWillChange]
    public function current(): PostObject
    {
        return current($this->posts);
    }

    public function next(): void
    {
        next($this->posts);
    }

    #[ReturnTypeWillChange]
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
}
