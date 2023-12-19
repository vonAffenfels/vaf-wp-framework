<?php

namespace VAF\WP\Framework\Metabox;

use VAF\WP\Framework\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(private readonly WordpressKernel $kernel, private readonly array $metaboxContainer)
    {
    }

    public function registerMetaboxes(): void
    {
        foreach ($this->metaboxContainer as $serviceId => $metaboxContainer) {
            foreach ($metaboxContainer as $data) {

                add_action('add_meta_boxes', function () use ($data, $serviceId) {
                    $methodName = $data['method'];

                    $screen = $data['screen'];
                    if ($data['supporting'] !== null) {
                        $screen = [
                            ...(
                            is_array($screen)
                                ? $screen
                                : (
                                    $screen === null
                                    ? []
                                    : [$screen]
                                )
                            ),
                            ...get_post_types_by_support($data['supporting']),
                        ];

                        if(empty($screen) && empty(get_post_types_by_support($data['supporting']))) {
                            return;
                        }
                    }

                    add_meta_box($data['id'], $data['title'], function () use ($serviceId, $methodName) {
                        $metaboxContainer = $this->kernel->getContainer()->get($serviceId);
                        return $metaboxContainer->$methodName();
                    }, $screen, $data['context'], $data['priority']);
                }, 5);
            }
        }
    }
}
