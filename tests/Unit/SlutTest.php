<?php

namespace VAF\WP\FrameworkTests\Unit;

use VAF\WP\Framework\Slug;
use VAF\WP\FrameworkTests\TestCase;

class SlutTest extends TestCase
{

    /**
     * @test
     */
    public function should_keep_a_to_z()
    {
        $slug = Slug::fromName('abcdefghijklmnopqrstuvwxyz');

        $this->assertEquals('abcdefghijklmnopqrstuvwxyz', (string)$slug);
    }

    /**
     * @test
     */
    public function should_keep_capital_a_to_z()
    {
        $slug = Slug::fromName('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        $this->assertEquals('ABCDEFGHIJKLMNOPQRSTUVWXYZ', (string)$slug);
    }
}
