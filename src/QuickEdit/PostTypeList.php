<?php

namespace VAF\WP\Framework\QuickEdit;

use VAF\WP\Framework\Metabox\EmptySupportingScreensException;

class PostTypeList
{

    private string|array|null $postTypes;
    private ?string $feature;
    private array $supportingPostTypes = [];

    public static function fromPostTypes(string|array|null $screen): self
    {
        $screenList = new static();

        $screenList->postTypes = $screen;

        return $screenList;
    }

    public function withSupporting(string|null $feature, callable $postTypesFromFeature): self
    {
        $screenList = clone $this;

        $screenList->feature = $feature;
        $screenList->supportingPostTypes = $feature === null ? [] : $postTypesFromFeature($feature);

        return $screenList;
    }

    public function postTypes(): string|array|null
    {
        if ($this->feature === null) {
            return $this->postTypes;
        }

        if($this->postTypes === null && empty($this->supportingPostTypes)) {
            throw new EmptySupportingPostTypesException();
        }

        if($this->postTypes === null) {
            return $this->supportingPostTypes;
        }

        return [$this->postTypes, ...$this->supportingPostTypes];
    }

}
