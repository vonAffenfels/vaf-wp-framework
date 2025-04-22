<?php

uses(\VAF\WP\FrameworkTests\TestCase::class);
use VAF\WP\Framework\Filter\Filter;
use VAF\WP\Framework\Filter\WordpressFilters;

beforeEach(function () {
    $mockFilters = Mockery::spy(WordpressFilters::class);

    Filter::fakeFilters(
        $mockFilters
    );
    $this->mockFilters = $mockFilters;
});

test('should call filter with passed arguments', function () {
    $this->mockFilters->shouldReceive('resultFrom')
        ->once()
        ->with('filter-name', 'arg1', 'arg2');

    Filter::fromName('filter-name')
        ->result('arg1', 'arg2');
});

test('should return filter result', function () {
    $this->mockFilters->shouldReceive('resultFrom')
        ->andReturn('filter-result');

    $result = Filter::fromName('filter-name')
        ->result('result');

    expect($result)->toEqual('filter-result');
});

test('should pass result through legacy filters', function () {
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

    expect($result)->toEqual('final-result');
});
