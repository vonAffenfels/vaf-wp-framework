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
                $this->registerBulkEditField($serviceId, $data);
            }

        }
    }

    private function registerBulkEditField($serviceId, $data)
    {
        add_action('admin_init', function () use ($data) {
            $postTypes = PostTypeList::fromPostTypes($data['postTypes'])->withSupporting($data['supporting'], fn($feature) => get_post_types_by_support($feature))
                ->postTypes();

            foreach ($postTypes as $postType) {
                add_action("manage_{$postType}_posts_columns", function ($columns) use ($data) {
                    return [
                        ...$columns,
                        $data['name'] => $data['title'],
                    ];
                });

                add_filter("manage_edit-{$postType}_columns", function ($columns) use ($data) {
                    return [
                        ...$columns,
                        $data['name'] => $data['title'],
                    ];
                }, 9999);
            }
        });

        add_action('bulk_edit_custom_box', function ($columnName) use ($serviceId, $data) {
            if ($columnName !== $data['name']) {
                return;
            }

            $methodName = $data['method'];
            echo $this->kernel->getContainer()->get($serviceId)->$methodName();
        });

        add_action('hidden_columns', function ($columns) use ($data) {
            return [
                ...$columns,
                $data['name'],
            ];
        });
    }
}
