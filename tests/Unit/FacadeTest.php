<?php

use VAF\WP\Framework\Facade\Facade;
use VAF\WP\Framework\Facade\Attribute\AsFacade;
use VAF\WP\Framework\Kernel\WordpressKernel;
use Symfony\Component\DependencyInjection\Container;

class TestService
{
    public function doSomething(): string
    {
        return 'something done';
    }
    
    public function doSomethingWithArgs(string $arg1, int $arg2): string
    {
        return "args: $arg1, $arg2";
    }
}

#[AsFacade(TestService::class)]
class TestServiceFacade extends Facade
{
}

beforeEach(function () {
    Facade::clearResolvedInstances();
});

test('facade can call methods on underlying service', function () {
    $container = new Container();
    $testService = new TestService();
    $container->set(TestService::class, $testService);
    
    $kernel = $this->createMock(WordpressKernel::class);
    $kernel->method('getContainer')->willReturn($container);
    
    Facade::setKernel($kernel);
    
    expect(TestServiceFacade::doSomething())->toBe('something done');
});

test('facade can call methods with arguments', function () {
    $container = new Container();
    $testService = new TestService();
    $container->set(TestService::class, $testService);
    
    $kernel = $this->createMock(WordpressKernel::class);
    $kernel->method('getContainer')->willReturn($container);
    
    Facade::setKernel($kernel);
    
    expect(TestServiceFacade::doSomethingWithArgs('hello', 42))->toBe('args: hello, 42');
});

test('facade caches resolved instances', function () {
    $container = new Container();
    $testService = new TestService();
    $container->set(TestService::class, $testService);
    
    $kernel = $this->createMock(WordpressKernel::class);
    $kernel->method('getContainer')->willReturn($container);
    
    Facade::setKernel($kernel);
    
    // First call
    TestServiceFacade::doSomething();
    
    // Second call should use cached instance
    expect(TestServiceFacade::doSomething())->toBe('something done');
});

test('facade throws exception when kernel not set', function () {
    Facade::setKernel(null);
    
    TestServiceFacade::doSomething();
})->throws(LogicException::class, 'Facade kernel has not been set.');

test('clear resolved instances removes all cached instances', function () {
    $container = new Container();
    $testService = new TestService();
    $container->set(TestService::class, $testService);
    
    $kernel = $this->createMock(WordpressKernel::class);
    $kernel->method('getContainer')->willReturn($container);
    
    Facade::setKernel($kernel);
    
    // Cache instance
    TestServiceFacade::doSomething();
    
    // Clear cache
    Facade::clearResolvedInstances();
    
    // This should resolve again
    expect(TestServiceFacade::doSomething())->toBe('something done');
});

test('clear resolved instance removes specific cached instance', function () {
    $container = new Container();
    $testService = new TestService();
    $container->set(TestService::class, $testService);
    
    $kernel = $this->createMock(WordpressKernel::class);
    $kernel->method('getContainer')->willReturn($container);
    
    Facade::setKernel($kernel);
    
    // Cache instance
    TestServiceFacade::doSomething();
    
    // Clear specific cache
    Facade::clearResolvedInstance(TestService::class);
    
    // This should resolve again
    expect(TestServiceFacade::doSomething())->toBe('something done');
});
