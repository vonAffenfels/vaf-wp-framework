<?php

namespace VAF\WP\Framework\BulkEdit;

use VAF\WP\Framework\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(private readonly WordpressKernel $kernel, private readonly array $bulkeditContainer)
    {
    }

    public function registerMetaboxes(): void
    {
        foreach ($this->bulkeditContainer as $serviceId => $bulkeditContainer) {
            foreach ($bulkeditContainer as $data) {

                add_action('add_meta_boxes', function () use ($data, $serviceId) {
                    $this->registerBulkEditField($serviceId, $data);


                    try {
                    } catch (EmptySupportingPostTypesException) {
                        // should only show up on supporting post types but none are registered and registering with
                        //empty array would result in showing up on all post types
                    }
                }, 5);
            }

        }
    }

    public function registerBulkEditField($serviceId, $data)
    {
        $methodName = $data['method'];

        add_action('admin_init', function () use ($data) {
            $post_types = PostTypeList::fromPostTypes($data['postTypes'])->withSupporting($data['supporting'], fn($feature) => get_post_types_by_support($feature))
                ->postTypes();

            foreach ($post_types as $post_type => $label) {
                add_action("manage_{$post_type}_posts_columns", function ($columns) use ($data) {
                    return [
                        ...$columns,
                        $data['name'] => $data['title'],
                    ];
                });

                add_filter("manage_edit-{$post_type}_columns", function ($columns) use ($data) {
                    return [
                        ...$columns,
                        $data['name'] => $data['title'],
                    ];
                }, 9999);
            }
        });

        add_action('bulk_edit_custom_box', function ($columnName, $serviceId, $data) {
            if ($columnName !== $data['name']) {
                return;
            }

            $methodName = $data['method'];
            echo $this->kernel->getContainer()->get($serviceId)->$methodName();
        });

        add_action('hidden_columns', function ($columns, $data) {
            return [
                ...$columns,
                $data['name'],
            ];
        });
    }
}
