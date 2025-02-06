<?php

namespace VAF\WP\Framework\QuickEdit;

use Closure;

class QuickEditField
{

    public readonly Closure $formField;
    public readonly Closure $data;

    public static function fromFormFieldData(
        callable $formField,
        callable $data
    ): self
    {
    	$quickEditField = new static();

        $quickEditField->formField = $formField;
        $quickEditField->data = $data;

    	return $quickEditField;
    }

}
