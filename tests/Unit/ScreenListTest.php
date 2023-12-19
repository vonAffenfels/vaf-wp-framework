<?php

namespace VAF\WP\FrameworkTests\Unit;

use VAF\WP\Framework\Metabox\EmptySupportingScreensException;
use VAF\WP\Framework\Metabox\ScreenList;
use VAF\WP\FrameworkTests\TestCase;

class ScreenListTest extends TestCase
{

    /**
     * @test
     */
    public function should_return_screen_if_supported_is_null()
    {
        $screens = ScreenList::fromScreen('expected screen')
            ->withSupporting(null, fn() => [])
            ->screens();

        $this->assertEquals('expected screen', $screens);
    }

    /**
     * @test
     */
    public function should_return_screen_and_supporting_post_type_in_array()
    {
        $screens = ScreenList::fromScreen('expected screen')
            ->withSupporting('feature', fn() => ['expected post type'])
            ->screens();

        $this->assertEquals(['expected screen', 'expected post type'], $screens);
    }

    /**
     * @test
     */
    public function should_only_return_supporting_post_type_in_array_if_screen_is_null()
    {
        $screens = ScreenList::fromScreen(null)
            ->withSupporting('feature', fn() => ['expected post type'])
            ->screens();

        $this->assertEquals(['expected post type'], $screens);
    }

    /**
     * @test
     */
    public function should_throw_is_empty_exception_if_screen_is_null_and_supporting_screens_are_empty()
    {
        $this->expectException(EmptySupportingScreensException::class);
        ScreenList::fromScreen(null)
            ->withSupporting('feature', fn() => [])
            ->screens();
    }

    /**
     * @test
     */
    public function should_return_null_for_screen_null_and_no_supporting_given()
    {
        $screens = ScreenList::fromScreen(null)
            ->withSupporting(null, fn(string $feature) => [])
            ->screens();

        $this->assertEquals(null, $screens);
    }
}
