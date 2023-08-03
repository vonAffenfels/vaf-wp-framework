<?php

namespace VAF\WP\Framework\Wordpress;

use WP_Post;

abstract class PostObject
{
    private static array $postTypeMap = [
        'post' => Post::class,
        'page' => Page::class
    ];

    private array $metadata = [];

    final public static function getByID(int $postId): static|false
    {
        $post = WP_Post::get_instance($postId);
        return (false === $post) ? false : static::createFromWPPost($post);
    }

    final public static function createFromWPPost(WP_Post $post): static
    {
        $postType = $post->post_type;
        $class = self::$postTypeMap[$postType] ?? Post::class;
        return new $class($post);
    }

    protected function __construct(private readonly WP_Post $post)
    {
    }

    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function getMetadata(string $name): mixed
    {
        if (!isset($this->metadata[$name])) {
            $meta = get_post_meta($this->post->ID, $name, true);

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
        if (property_exists($this->post, $name)) {
            return $this->post->$name;
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
