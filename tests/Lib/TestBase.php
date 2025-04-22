<?php

namespace VAF\WP\FrameworkTests\Lib;

use Mockery;

class TestBase extends \VAF\WP\Framework\BaseWordpress
{
    public static function forName(string $name)
    {
        return new self($name, '', '');
    }

    protected function createKernel(): \VAF\WP\Framework\Kernel\Kernel
    {
        return Mockery::mock(\VAF\WP\Framework\Kernel\Kernel::class);
    }
}
