<?php

namespace VAF\WP\Framework\Tests\Unit;

use VAF\WP\Framework\Setting\Conversion;
use VAF\WP\Framework\Setting\Setting;

class TestSetting extends Setting
{
    public function getValue(?string $key = null)
    {
        return $this->get($key);
    }

    public function setValue($value, ?string $key = null): self
    {
        return $this->set($value, $key);
    }
}

class ConversionTestSetting extends Setting
{
    public function getValue(?string $key = null)
    {
        return $this->get($key);
    }

    public function setValue($value, ?string $key = null): self
    {
        return $this->set($value, $key);
    }

    protected function conversion(): Conversion
    {
        return new Conversion(
            fromDb: fn($value) => json_decode($value, true),
            toDb: fn($value) => json_encode($value),
            fromInput: fn($value) => is_string($value) ? json_decode($value, true) : $value
        );
    }
}

beforeEach(function () {
    Setting::clearFakes();
});

afterEach(function () {
    Setting::clearFakes();
});

it('can fake a setting value', function () {
    Setting::fakeSetting('test_setting', 'fake_value');
    
    $setting = new TestSetting('test_setting', 'test_plugin', 'default_value');
    
    expect($setting->getValue())->toBe('fake_value');
});

it('returns default value when not faked', function () {
    $setting = new TestSetting('test_setting', 'test_plugin', 'default_value');
    
    expect($setting->getValue())->toBe('default_value');
});

it('can fake array values and access with keys', function () {
    $fakeArray = [
        'key1' => 'value1',
        'key2' => 'value2'
    ];
    
    Setting::fakeSetting('array_setting', $fakeArray);
    
    $setting = new TestSetting('array_setting', 'test_plugin', ['key1' => 'default1', 'key3' => 'default3']);
    
    expect($setting->getValue())->toBe($fakeArray);
    expect($setting->getValue('key1'))->toBe('value1');
    expect($setting->getValue('key2'))->toBe('value2');
    // When accessing non-existent key, it should return the default for that key
    expect($setting->getValue('key3'))->toBe('default3');
});

it('updates fake value when saving', function () {
    Setting::fakeSetting('test_setting', 'initial_value');
    
    $setting = new TestSetting('test_setting', 'test_plugin');
    $setting->setValue('updated_value');
    
    // Create a new instance to verify the fake was updated
    $setting2 = new TestSetting('test_setting', 'test_plugin');
    expect($setting2->getValue())->toBe('updated_value');
});

it('can check if a setting is faked', function () {
    expect(Setting::isFaked('test_setting'))->toBeFalse();
    
    Setting::fakeSetting('test_setting', 'value');
    
    expect(Setting::isFaked('test_setting'))->toBeTrue();
    
    Setting::clearFakes();
    
    expect(Setting::isFaked('test_setting'))->toBeFalse();
});

it('works with conversion functions', function () {
    $data = ['foo' => 'bar', 'baz' => 123];
    
    Setting::fakeSetting('json_setting', $data);
    
    $setting = new ConversionTestSetting('json_setting', 'test_plugin');
    
    expect($setting->getValue())->toBe($data);
});

it('handles setting with keys correctly when faked', function () {
    $initialData = ['existing' => 'value'];
    Setting::fakeSetting('keyed_setting', $initialData);
    
    $setting = new TestSetting('keyed_setting', 'test_plugin');
    expect($setting->getValue())->toBe($initialData);
    
    $setting->setValue('new_value', 'new_key');
    
    // The fake should now have both keys
    expect(Setting::isFaked('keyed_setting'))->toBeTrue();
    
    // Create a new instance to verify the fake was updated
    $setting2 = new TestSetting('keyed_setting', 'test_plugin');
    $value = $setting2->getValue();
    expect($value)->toBeArray();
    expect($value)->toHaveKey('existing');
    expect($value)->toHaveKey('new_key');
    expect($value['existing'])->toBe('value');
    expect($value['new_key'])->toBe('new_value');
});

it('clears all fakes with clearFakes', function () {
    Setting::fakeSetting('setting1', 'value1');
    Setting::fakeSetting('setting2', 'value2');
    
    expect(Setting::isFaked('setting1'))->toBeTrue();
    expect(Setting::isFaked('setting2'))->toBeTrue();
    
    Setting::clearFakes();
    
    expect(Setting::isFaked('setting1'))->toBeFalse();
    expect(Setting::isFaked('setting2'))->toBeFalse();
});

it('can be instantiated with a string base name', function () {
    Setting::fakeSetting('string_base_setting', 'test_value');
    
    $setting = new TestSetting('string_base_setting', 'my_plugin_name', 'default');
    
    expect($setting->getValue())->toBe('test_value');
});