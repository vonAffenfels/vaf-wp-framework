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
    public function should_change_capital_a_to_z_to_lowercase_a_to_z()
    {
        $slug = Slug::fromName('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        $this->assertEquals('abcdefghijklmnopqrstuvwxyz', (string)$slug);
    }

    /**
     * @test
     */
    public function should_change_anything_not_a_to_z_to_dash()
    {
        $slug = Slug::fromName('a b');

        $this->assertEquals('a-b', (string)$slug);
    }

    /**
     * @test
     */
    public function should_reduce_multiple_dashes_to_single_dash()
    {
        $slug = Slug::fromName('a!@#b');

        $this->assertEquals('a-b', (string)$slug);
    }
}
