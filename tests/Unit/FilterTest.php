<?php

namespace VAF\WP\FrameworkTests\Unit;

use Mockery;
use VAF\WP\Framework\Filter\Filter;
use VAF\WP\Framework\Filter\WordpressFilters;
use VAF\WP\FrameworkTests\TestCase;

class FilterTest extends TestCase
{
    private WordpressFilters|Mockery\LegacyMockInterface|Mockery\MockInterface $mockFilters;

    public function setUp(): void
    {
        parent::setUp();

        $mockFilters = Mockery::spy(WordpressFilters::class);

        Filter::fakeFilters(
            $mockFilters
        );
        $this->mockFilters = $mockFilters;
    }


    /**
     * @test
     */
    public function should_call_filter_with_passed_arguments()
    {
        $this->mockFilters->shouldReceive('resultFrom')
            ->once()
            ->with('filter-name', 'arg1', 'arg2');

        Filter::fromName('filter-name')
            ->result('arg1', 'arg2');
    }

    /**
     * @test
     */
    public function should_return_filter_result()
    {
        $this->mockFilters->shouldReceive('resultFrom')
            ->andReturn('filter-result');

        $result = Filter::fromName('filter-name')
            ->result('result');

        $this->assertEquals('filter-result', $result);
    }

    /**
     * @test
     */
    public function should_pass_result_through_legacy_filters()
    {
        $this->mockFilters->shouldReceive('resultFrom')
            ->once()
            ->with('legacy-name-1', 'initial-args')
            ->andReturn('legacy-name-1-result');
        $this->mockFilters->shouldReceive('resultFrom')
            ->once()
            ->with('legacy-name-2', 'legacy-name-1-result')
            ->andReturn('legacy-name-2-result');
        $this->mockFilters->shouldReceive('resultFrom')
            ->once()
            ->with('filter-name', 'legacy-name-2-result')
            ->andReturn('final-result');

        $result = Filter::fromName('filter-name')
            ->withLegacyName('legacy-name-1')
            ->withLegacyName('legacy-name-2')
            ->result('initial-args');

        $this->assertEquals('final-result', $result);
    }

}
