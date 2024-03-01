<?php

namespace VAF\WP\Framework\PostObjects;

use VAF\WP\Framework\Kernel\WordpressKernel;
use VAF\WP\Framework\System\Parameters\Parameter;
use VAF\WP\Framework\System\Parameters\ParameterBag;
use VAF\WP\Framework\Utils\ClassSystem;

class ExtensionLoader
{
    public function __construct(private readonly WordpressKernel $kernel, private readonly array $extensions)
    {
    }

    public function registerPostObjectExtensions(): void
    {
        foreach ($this->extensions as $extension) {
            foreach ($extension['postTypes'] ?? [] as $postType) {
                $hookName = 'vaf_wp_framework/post_type_ext/' . $postType . '/' . $extension['fieldName'];
                $parameterBag = ParameterBag::fromArray($extension['params']);

                add_filter(
                    $hookName,
                    function (
                        mixed $return,
                        PostObject $post,
                        array $parameters = []
                    ) use (
                        $parameterBag,
                        $extension
                    ): mixed {
                        $params = [];

                        /** @var Parameter $parameter */
                        foreach ($parameterBag->getParams() as $parameter) {
                            if ($parameter->getType() === 'string') {
                                if (!empty($parameters)) {
                                    $params[$parameter->getName()] = array_shift($parameters);
                                }
                            } elseif (ClassSystem::isExtendsOrImplements(PostObject::class, $parameter->getType())) {
                                $params[$parameter->getName()] = $post;
                            } elseif ($parameter->isServiceParam()) {
                                $params[$parameter->getName()] = $this->kernel->getContainer()->get(
                                    $parameter->getType()
                                );
                            }
                        }

                        $method = $extension['method'];
                        $container = $this->kernel->getContainer()->get($extension['serviceId']);
                        return $container->$method(...$params);
                    },
                    10,
                    3
                );
            }
        }
    }
}
