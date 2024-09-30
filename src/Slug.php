<?php

namespace VAF\WP\Framework;

class Slug
{

    private string $name;

    public static function fromName(string $name): self
    {
    	$slug = new static();

        $slug->name = $name;

    	return $slug;
    }

    public function __toString(): string
    {
        return preg_replace(
            '~[^a-zA-Z]~',
            '-',
            strtolower(
                $this->name,
            ),
        );
    }
}
