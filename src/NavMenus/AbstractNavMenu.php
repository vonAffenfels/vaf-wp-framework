<?php

namespace VAF\WP\Framework\NavMenus;

use VAF\WP\Framework\Filter\Filter;
use VAF\WP\Framework\PostObjects\PostObjectList;
use VAF\WP\Framework\PostObjects\PostObjectManager;
use VAF\WP\Framework\PostObjects\PostTypes\NavMenuItem;

abstract class AbstractNavMenu
{
    private int $id;

    private PostObjectList $items;

    public function __construct(
        private readonly PostObjectManager $postObjectManager,
        protected readonly string $slug
    ) {
        $this->id = $this->getMenuIdFromSlug();
        $this->loadMenu();
    }

    public function getItems(): PostObjectList
    {
        return $this->items;
    }

    private function loadMenu(): void
    {
        $this->items = new PostObjectList($this->postObjectManager, []);

        if ($this->id === 0) {
            return;
        }

        $items = wp_get_nav_menu_items($this->id);
        if (false === $items) {
            return;
        }

        _wp_menu_item_classes_by_context($items);

        /**
         * Default arguments from wp_nav_menu() function.
         *
         * @see wp_nav_menu()
         */
        $defaultArgsArray = array(
            'menu'            => '',
            'container'       => 'div',
            'container_class' => '',
            'container_id'    => '',
            'menu_class'      => 'menu',
            'menu_id'         => '',
            'echo'            => true,
            'fallback_cb'     => 'wp_page_menu',
            'before'          => '',
            'after'           => '',
            'link_before'     => '',
            'link_after'      => '',
            'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
            'item_spacing'    => 'preserve',
            'depth'           => 0,
            'walker'          => '',
            'theme_location'  => '',
        );

        /**
         * Improve compatibitility with third-party plugins.
         *
         * @see wp_nav_menu()
         */
        $defaultArgsArray = apply_filters('wp_nav_menu_args', $defaultArgsArray);
        $items = apply_filters('wp_nav_menu_objects', $items, (object)$defaultArgsArray);

        $index = [];
        foreach ($items as $item) {
            $item = $this->postObjectManager->getByWPPost($item);
            if (!$item instanceof NavMenuItem) {
                continue;
            }

            $index[$item->getId()] = $item;
        }

        /** @var NavMenuItem $item */
        foreach ($index as $item) {
            $parent = $item->getMenuItemParent();
            if (!empty($parent) && isset($index[$parent])) {
                $index[$parent]->addChild($item);
            } else {
                $this->items->addPost($item);
            }
        }
    }

    private function getMenuIdFromSlug(): int
    {
        $locations = get_nav_menu_locations();
        if (is_array($locations) && isset($locations[$this->slug])) {
            return Filter::fromName('wpml_object_id')->result($locations[$this->slug], 'nav_menu');
        }

        $menu = get_term_by('slug', $this->slug, 'nav_menu');
        if (false !== $menu) {
            return $menu->term_id;
        }

        $menu = get_term_by('name', $this->slug, 'nav_menu');
        if (false !== $menu) {
            return $menu->term_id;
        }

        return 0;
    }
}
