<?php


use VAF\WP\Framework\RestAPI\RestApiLink;
use VAF\WP\FrameworkTests\Lib\TestBase;

beforeEach(function () {
    $this->base = TestBase::forName('plugin-name');
});

describe('namespace', function () {

    it('should start with plugin name', function () {
        $link = RestApiLink::forNamespacePluginRoute('', $this->base, '');

        expect($link->namespace())->toBe('plugin-name');
    });

    it('should add the slash namespace after the plugin name if it is not empty', function () {
        $link = RestApiLink::forNamespacePluginRoute('expected-namespace', $this->base, '');

        expect($link->namespace())->toBe('plugin-name/expected-namespace');
    });

    it('should not add extra slash if it is already present', function () {
        $link = RestApiLink::forNamespacePluginRoute('/expected-namespace', $this->base, '');

        expect($link->namespace())->toBe('plugin-name/expected-namespace');
    });
});

describe('route', function () {

    it('should start with slash', function () {
        $link = RestApiLink::forNamespacePluginRoute('', $this->base, 'expected-route');

        expect($link->uri())->toBe('/expected-route');
    });

    it('should no duplicate slash if present in route', function () {
        $link = RestApiLink::forNamespacePluginRoute('', $this->base, '/expected-route');

        expect($link->uri())->toBe('/expected-route');
    });
});

describe('publicUrl', function () {

    it('should return wordpress rest_url return value', function () {
        $link = RestApiLink::forNamespacePluginRoute('', $this->base, 'expected-route');

        expect($link->withFakeWordpressCall(fn() => 'rest_url return')->publicUrl())->toBe('rest_url return');
    });

    it('pass namespace slash uri to wordpress rest_url', function () {
        $actualParameter = '';

        $link = RestApiLink::forNamespacePluginRoute('expected-namespace', $this->base, 'expected-route');

        $link->withFakeWordpressCall(function ($passedParameter) use (&$actualParameter) {
            $actualParameter = $passedParameter;
            return '';
        })->publicUrl();

        expect($actualParameter)->toBe($link->namespace().$link->uri());
    });
});
