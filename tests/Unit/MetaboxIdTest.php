<?php

uses(\VAF\WP\FrameworkTests\TestCase::class);
use VAF\WP\Framework\Metabox\MetaboxId;

test('should generate classname without namespace underscore methodname', function () {
    $id = (string)MetaboxId::fromClassMethodName('Namespace\\Subnamespace\\Classname', 'methodName');

    expect($id)->toEqual('classname_methodname');
});
