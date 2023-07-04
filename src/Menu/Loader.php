<?php

namespace VAF\WP\Framework\Menu;

use VAF\WP\Framework\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(private readonly WordpressKernel $kernel, private readonly array $menuData)
    {
    }

    private function registerMenu(array $menuData): void
    {
        $callback = function () use ($menuData): void {
            $passedParameters = [];
            foreach ($menuData['serviceParams'] as $param => $service) {
                $passedParameters[$param] = $this->kernel->getContainer()->get($service);
            }

            $methodName = $menuData['method'];

            $menuContainer = $this->kernel->getContainer()->get($menuData['service']);
            $menuContainer->$methodName(...$passedParameters);
        };

        if (empty($menuData['parent'])) {
            add_menu_page(
                $menuData['menuTitle'],
                $menuData['menuTitle'],
                $menuData['capability'],
                $menuData['slug'],
                $callback,
                $menuData['icon'],
                $menuData['position']
            );

            if (!empty($menuData['subMenuTitle'])) {
                // Add atleast one child to give the option to name the submenu item differently
                // when having children
                add_submenu_page(
                    $menuData['slug'],
                    $menuData['menuTitle'],
                    $menuData['subMenuTitle'],
                    $menuData['capability'],
                    $menuData['slug'],
                    '__return_false',
                    $menuData['position']
                );
            }
        } else {
            add_submenu_page(
                $menuData['parent'],
                $menuData['menuTitle'],
                $menuData['menuTitle'],
                $menuData['capability'],
                $menuData['slug'],
                $callback,
                $menuData['position']
            );
        }
    }

    public function registerMenus(): void
    {
        foreach ($this->menuData['top'] as $menuData) {
            $this->registerMenu($menuData);
        }

        foreach ($this->menuData['sub'] as $menuData) {
            $this->registerMenu($menuData);
        }
    }
}
