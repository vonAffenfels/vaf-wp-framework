<?php

namespace VAF\WP\Framework\BulkEdit;

use VAF\WP\Framework\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(private readonly WordpressKernel $kernel, private readonly array $bulkeditContainer)
    {
    }

    public function registerBulkEditFields(): void
    {
        foreach ($this->bulkeditContainer as $serviceId => $bulkeditContainer) {
            foreach ($bulkeditContainer as $data) {

                add_action('add_meta_boxes', function () use ($data, $serviceId) {
                    $this->registerBulkEditField($serviceId, $data);
                }, 5);
            }

        }
    }

    private function registerBulkEditField($serviceId, $data)
    {
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

        add_action('bulk_edit_custom_box', function ($columnName, $data) use ($serviceId) {
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
