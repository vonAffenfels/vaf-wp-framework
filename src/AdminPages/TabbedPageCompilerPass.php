<?php

namespace VAF\WP\Framework\AdminPages;

use Exception;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VAF\WP\Framework\AdminPages\Attributes\IsTabbedPage;

final class TabbedPageCompilerPass implements CompilerPassInterface
{
    /**
     * @throws Exception
     */
    public function process(ContainerBuilder $container): void
    {
        $tabbedPages = $container->findTaggedServiceIds('adminpages.tabbed');

        foreach ($tabbedPages as $id => $tags) {
            $definition = $container->findDefinition($id);

            $classReflection = new ReflectionClass($definition->getClass());
            $attribute = $classReflection->getAttributes(IsTabbedPage::class);
            if (empty($attribute)) {
                continue;
            }

            /** @var IsTabbedPage $attrInstance */
            $attrInstance = $attribute[0]->newInstance();

            $definition->setArgument('$pageTitle', $attrInstance->pageTitle);
            $definition->setArgument('$pageVar', $attrInstance->pageVar);
            $definition->setArgument('$defaultSlug', $attrInstance->defaultSlug);
        }
    }
}
