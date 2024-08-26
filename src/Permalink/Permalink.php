<?php

namespace VAF\WP\Framework\Permalink;

class Permalink
{

    static ?PermalinkResolver $permalinkResolver = null;
    private int $postId;

    public static function fake(PermalinkResolver $permalinkResolver)
    {
        self::$permalinkResolver = $permalinkResolver;
    }

    public static function fakePassthrough()
    {
        self::$permalinkResolver = new PermalinkPassthroughResolver();
    }

    public static function fromPostId(int $postId): self
    {
    	$permalink = new static();

        $permalink->postId = $postId;

    	return $permalink;
    }

    public function __toString(): string
    {
        return self::resolver()->permalinkForPostId($this->postId);
    }

    private static function resolver(): PermalinkResolver
    {
        if(self::$permalinkResolver === null) {
            self::$permalinkResolver = new PermalinkResolver();
        }

        return self::$permalinkResolver;
    }

}
