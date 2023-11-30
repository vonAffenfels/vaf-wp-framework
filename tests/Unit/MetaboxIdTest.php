<?php

namespace VAF\WP\FrameworkTests\Unit;

use VAF\WP\Framework\Metabox\MetaboxId;
use VAF\WP\FrameworkTests\TestCase;

class MetaboxIdTest extends TestCase
{

    /**
     * @test
     */
    public function should_generate_classname_without_namespace_underscore_methodname()
    {
        $id = (string)MetaboxId::fromClassMethodName('Namespace\\Subnamespace\\Classname', 'methodName');

        $this->assertEquals('classname_methodname', $id);
    }

}
