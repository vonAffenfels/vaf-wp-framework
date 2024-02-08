<?php

namespace VAF\WP\Framework\Queries;

use VAF\WP\Framework\PostObjects\PostObjectList;
use VAF\WP\Framework\PostObjects\PostObjectManager;
use WP_Query;

abstract class AbstractQuery
{
    private ?int $numPosts = null;
    private ?int $page = null;
    private array $orders = [];

    public function __construct(private readonly PostObjectManager $postObjectManager)
    {
    }

    public function setNumPosts(int $numPosts): self
    {
        $this->numPosts = $numPosts;
        return $this;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function addOrderField(string $field, OrderBy $order): self
    {

    }

    abstract protected function configureQuery(): void;

    private function getQuery(): array
    {
        $query = [];

        if (!is_null($this->numPosts)) {
            $query['posts_per_page'] = $this->numPosts;
        }

        if (!is_null($this->page)) {
            $query['paged'] = $this->page;
        }

        return $query;
    }

    public function getPosts(): PostObjectList
    {
        $this->configureQuery();

        $query = $this->getQuery();
        $wpQuery = new WP_Query($query);

        return new PostObjectList($this->postObjectManager, $wpQuery->posts);
    }
}
