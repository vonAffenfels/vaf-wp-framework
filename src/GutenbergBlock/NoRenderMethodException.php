<?php

namespace VAF\WP\Framework\GutenbergBlock;

class NoRenderMethodException extends \LogicException
{

    public function __construct($class)
    {
        parent::__construct(
            implode(
                " ",
                [
                    "Class $class does not have a render method defined.",
                    "Please implement the following:",
                    "public function render(array \$blockAttributes, \$content): string;",
                    "Or check the documentation on how to use a model for \$blockAttributes instead",
                ]
            )
        );
    }

}
