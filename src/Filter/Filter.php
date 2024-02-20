<?php

namespace VAF\WP\Framework\Filter;

class Filter
{
    private static ?WordpressFilters $filters = null;

    private readonly string $name;
    private array $legacyNames = [];

    public static function fromName(string $name): self
    {
        $filter = new static();

        $filter->name = $name;

        return $filter;
    }

    public static function resetFake(): void
    {
        self::$filters = null;
    }

    public static function fakeFilters(WordpressFilters $wordpressFilters): void
    {
        self::$filters = $wordpressFilters;
    }

    public function result($result, ...$args)
    {
        return $this->filters()->resultFrom(
            $this->name,
            array_reduce($this->legacyNames, function ($previousResult, Filter $legacyFilter) use ($args) {
                return $legacyFilter->result($previousResult, ...$args);
            }, $result),
            ...$args
        );
    }

    private function filters(): WordpressFilters
    {
        if (self::$filters === null) {
            self::$filters = new WordpressFilters();
        }

        return self::$filters;
    }

    public function withLegacyName(string $legacyName): self
    {
        $clone = clone $this;

        $clone->legacyNames = [...$this->legacyNames, Filter::fromName($legacyName)];

        return $clone;
    }
}
