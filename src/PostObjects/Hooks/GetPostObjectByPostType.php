<?php

namespace VAF\WP\Framework\PostObjects\Hooks;

use VAF\WP\Framework\BaseWordpress;
use VAF\WP\Framework\Hook\Attribute\AsHookContainer;
use VAF\WP\Framework\Hook\Attribute\Hook;
use VAF\WP\Framework\PostObjects\PostObject;
use VAF\WP\Framework\PostObjects\PostObjectManager;

#[AsHookContainer]
class GetPostObjectByPostType
{
    #[Hook(PostObjectManager::HOOK_GET_POST_OBJECT_BY_POST_TYPE)]
    public function getPostObjectList(
        BaseWordpress $base,
        PostObjectManager $manager,
        ?PostObject $obj,
        string $postType
    ): ?PostObject {
        $class = $manager->getInternalClassForPostType($postType);
        if (!is_null($class)) {
            $obj = $base->getContainer()->get($class);
        }

        return $obj;
    }
}
