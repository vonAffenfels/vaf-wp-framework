<?php

namespace VAF\WP\FrameworkTests\Unit;

use VAF\WP\Framework\Permalink\Permalink;
use VAF\WP\Framework\Permalink\PermalinkResolver;
use VAF\WP\FrameworkTests\TestCase;

class PermalinkTest extends TestCase
{

    /**
     * @test
     */
    public function should_be_able_to_fake_permalink_before_creating_permalinnk()
    {
        $resolver = \Mockery::mock(PermalinkResolver::class);
        $resolver->shouldReceive('permalinkForPostId')->with(15)->andReturn('expected permalink');

        Permalink::fake($resolver);
        $permalink = Permalink::fromPostId(15);

        $this->assertEquals('expected permalink', (string)$permalink);
    }

    /**
     * @test
     */
    public function should_be_able_to_fake_permalink_after_creating_permalinnk()
    {
        $resolver = \Mockery::mock(PermalinkResolver::class);
        $resolver->shouldReceive('permalinkForPostId')->with(15)->andReturn('expected permalink');

        $permalink = Permalink::fromPostId(15);
        Permalink::fake($resolver);

        $this->assertEquals('expected permalink', (string)$permalink);
    }
}
