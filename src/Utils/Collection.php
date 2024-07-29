<?php

namespace VAF\WP\Framework\Utils;

class Collection
{
    private array $data;

    public static function make(array $data = []): self
    {
    	$collection = new static();

        $collection->data = $data;

    	return $collection;
    }

    public function doesNotContain(callable $callback): bool
    {
        return !$this->contains($callback);
    }

    public function contains(callable $callback): bool
    {
        foreach($this->data as $entry) {
            if($callback($entry)) {
                return true;
            }
        }

        return false;
    }
}
