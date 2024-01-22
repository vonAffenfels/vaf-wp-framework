<?php

namespace VAF\WP\FrameworkTests;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use VAF\WP\Framework\Filter\Filter;

class TestCase extends MockeryTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        Filter::resetFake();
    }

}
