<?php

namespace VAF\WP\Framework\PostObjects;

use LogicException;
use WP_Post;

abstract class PostObject
{
    private array $metadata = [];

    private ?WP_Post $post = null;

    public function setPost(WP_Post $post): static
    {
        $this->post = $post;
        return $this;
    }

    private function getPost(): WP_Post
    {
        if (is_null($this->post)) {
            throw new LogicException('PostObject not initialized!');
        }

        return $this->post;
    }

    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function getMetadata(string $name): mixed
    {
        if (!isset($this->metadata[$name])) {
            $meta = get_post_meta($this->getPost()->ID, $name, true);

            if (is_serialized($meta)) {
                $meta = @unserialize(trim($meta));
            } elseif ($this->isJson($meta)) {
                $meta = json_decode($meta, true);
            }

            $this->metadata[$name] = $meta;
        }
        return $this->metadata[$name];
    }

    public function get(string $name): mixed
    {
        if (property_exists($this->getPost(), $name)) {
            return $this->getPost()->$name;
        }

        return $this->getMetadata($name);
    }

    public function getID(): int
    {
        return $this->get('ID');
    }

    public function getContent(): string
    {
        return $this->get('post_content');
    }

    public function getTitle(): string
    {
        return $this->get('post_title');
    }

    public function getSlug(): string
    {
        return $this->get('post_name');
    }
}
