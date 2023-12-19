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


                    try {
                        add_meta_box(
                            $data['id'],
                            $data['title'],
                            function () use ($serviceId, $methodName) {
                                $metaboxContainer = $this->kernel->getContainer()->get($serviceId);
                                return $metaboxContainer->$methodName();
                            },
                            ScreenList::fromScreen($data['screen'])
                                ->withSupporting($data['supporting'], fn($feature) => get_post_types_by_support($feature))
                                ->screens(),
                            $data['context'],
                            $data['priority']
                        );
                    } catch (EmptySupportingScreensException) {
                        // should only show up on supporting post types but none are registered and registering with
                        //empty array would result in showing up on all post types
                    }
                }, 5);
            }
        }
    }
}
