<?php

namespace VAF\WP\Framework\QuickEdit;

use VAF\WP\Framework\Kernel\WordpressKernel;

final class Loader
{
    public function __construct(private readonly WordpressKernel $kernel, private readonly array $quickeditContainer)
    {
    }

    public function registerQuickEditFields(): void
    {
        foreach ($this->quickeditContainer as $serviceId => $quickeditContainer) {
            foreach ($quickeditContainer as $data) {
                $this->registerQuickEditField($serviceId, $data);
            }

        }
    }

    private function registerQuickEditField($serviceId, $data)
    {
        add_action('admin_init', function () use ($data, $serviceId) {
            $postTypes = PostTypeList::fromPostTypes($data['postTypes'])->withSupporting($data['supporting'], fn($feature) => get_post_types_by_support($feature))
                ->postTypes();

            foreach ($postTypes as $postType) {
                add_action("manage_edit-{$postType}_columns", function ($columns) use ($data) {
                    return [
                        ...$columns,
                        $data['name'] => $data['title'],
                    ];
                }, 9999);

                add_action("manage_{$postType}_posts_custom_column", function ($columnName, $postId) use ($data, $serviceId) {
                    if ($columnName !== $data['name']) {
                        return;
                    }

                    $methodName = $data['method'];
                    echo json_encode(
                        ($this->kernel->getContainer()->get($serviceId)->{$methodName}()->data)(
                            new QuickEditPostDataEvent(postId: $postId, columnName: $data['name'])
                        )
                    );
                }, 9999, accepted_args: 2);
            }
        });

        add_action('quick_edit_custom_box', function ($columnName, $postId) use ($serviceId, $data) {
            if ($columnName !== $data['name']) {
                return;
            }

            $methodName = $data['method'];
            echo ($this->kernel->getContainer()->get($serviceId)->{$methodName}()->formField)(
                new RenderQuickEditFormFieldEvent(
                    postId: $postId,
                    columnName: $data['name']
                )
            );
        }, accepted_args: 2);

        add_action('hidden_columns', function ($columns) use ($data) {
            return [
                ...$columns,
                $data['name'],
            ];
        });
    }
}
