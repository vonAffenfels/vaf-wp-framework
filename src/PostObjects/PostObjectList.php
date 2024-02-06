<?php

namespace VAF\WP\Framework\PostObjects;

use Iterator;
use ReturnTypeWillChange;
use WP_Post;

class PostObjectList implements Iterator
{
    private array $posts;

    public function __construct(private readonly PostObjectManager $manager, array $posts)
    {
        $this->setPosts($posts);
    }

    public function setPosts(array $posts): self
    {
        $this->posts = array_map(function (WP_Post|int $post): PostObject {
            if ($post instanceof WP_Post) {
                return $this->manager->getByWPPost($post);
            } else {
                return $this->manager->getById($post);
            }
        }, array_filter($posts, function ($post): bool {
            return $post instanceof WP_Post || is_int($post);
        }));

        return $this;
    }

    public function addPost(PostObject|WP_Post|int $post): self
    {
        if ($post instanceof WP_Post) {
            $post = $this->manager->getByWPPost($post);
        } elseif (is_int($post)) {
            $post = $this->manager->getById($post);
        }

        $this->posts[] = $post;

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
}
