<?php

namespace VAF\WP\Framework\PostObjects;

use VAF\WP\Framework\PostObjects\Attributes\PostType;

#[PostType('nav_menu_item')]
class NavMenuItem extends PostObject
{
    private PostObjectList $children;

    private ?array $classes = null;

    public function __construct(PostObjectManager $postObjectManager)
    {
        $this->children = new PostObjectList($postObjectManager, []);
    }

    public function getMenuItemParent(): int
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->getPost()->menu_item_parent;
    }

    public function getUrl(): string
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->getPost()->url;
    }

    public function getTitle(): string
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->getPost()->title;
    }

    public function getTarget(): string
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->getPost()->target;
    }

    private function initializeClasses(): void
    {
        if (is_null($this->classes)) {
            /** @noinspection PhpUndefinedFieldInspection */
            $this->classes = apply_filters(
                'nav_menu_css_class',
                array_filter($this->getPost()->classes, function ($item): bool {
                    return !empty($item);
                }),
                $this->getPost(),
                new \stdClass(),
                0
            );

            $this->classes[] = 'menu-item-' . $this->getId();
        }
    }

    private function addClass(string $class): self
    {
        $this->initializeClasses();

        if (!in_array($class, $this->classes)) {
            $this->classes[] = $class;
        }

        return $this;
    }

    public function getClasses(string|false $glue = false): string|array
    {
        $this->initializeClasses();

        if (false !== $glue) {
            return implode($glue, $this->classes);
        }

        return $this->classes;
    }

    /************
     * Children *
     ************/
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    public function getChildren(): PostObjectList
    {
        return $this->children;
    }

    public function addChild(NavMenuItem $item): self
    {
        $this->addClass('menu-item-has-children');
        $this->children->addPost($item);
        return $this;
    }
}
