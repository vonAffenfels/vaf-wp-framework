<?php

namespace VAF\WP\Framework\Tests\Unit;

use VAF\WP\Framework\Setting\Conversion;
use VAF\WP\Framework\Setting\Setting;
use VAF\WP\Framework\Wordpress\Wordpress;

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
    Wordpress::resetFake();
});

afterEach(function () {
    Setting::clearFakes();
    Wordpress::resetFake();
});

it('can fake a setting value', function () {
    Setting::fakeSetting('test_setting', 'fake_value');
    
    $setting = new TestSetting('test_setting', 'test_plugin', 'default_value');
    
    expect($setting->getValue())->toBe('fake_value');
});

it('returns default value when not faked', function () {
    Wordpress::fake();
    Wordpress::mock()->shouldReceive('get_option')
        ->with('test_plugin_test_setting', 'default_value')
        ->andReturn('default_value');
    
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
    $jsonData = json_encode($data); // Database format
    
    Setting::fakeSetting('json_setting', $jsonData);
    
    $setting = new ConversionTestSetting('json_setting', 'test_plugin');
    
    expect($setting->getValue())->toBe($data); // Converted from JSON to array
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

describe('Fake conversion behavior', function () {
    it('applies fromDb conversion when getting faked values', function () {
        // Fake a JSON string (database format)
        Setting::fakeSetting('json_convert', '{"name":"test","count":42}');
        
        $setting = new ConversionTestSetting('json_convert', 'test_plugin');
        $value = $setting->getValue();
        
        expect($value)->toBeArray();
        expect($value)->toBe(['name' => 'test', 'count' => 42]);
    });

    it('applies toDb conversion when saving faked values', function () {
        // Start with a JSON string in database format
        Setting::fakeSetting('json_save', '{}');
        
        $setting = new ConversionTestSetting('json_save', 'test_plugin');
        $setting->setValue(['key' => 'new_value']);
        
        // Check that the fake was updated with the JSON string (toDb conversion)
        expect(Setting::isFaked('json_save'))->toBeTrue();
        
        // Create a new instance to verify the fake contains JSON
        $setting2 = new ConversionTestSetting('json_save', 'test_plugin');
        expect($setting2->getValue())->toBe(['key' => 'new_value']);
    });

    it('handles round-trip conversion correctly with fakes', function () {
        // Start with database format
        $originalData = ['items' => [1, 2, 3], 'active' => true];
        Setting::fakeSetting('round_trip', json_encode($originalData));
        
        // Get and modify
        $setting = new ConversionTestSetting('round_trip', 'test_plugin');
        expect($setting->getValue())->toBe($originalData);
        
        // Update the value
        $newData = ['items' => [4, 5, 6], 'active' => false];
        $setting->setValue($newData);
        
        // Verify with a new instance
        $setting2 = new ConversionTestSetting('round_trip', 'test_plugin');
        expect($setting2->getValue())->toBe($newData);
    });

    it('applies boolean conversion with fakes', function () {
        // Create a boolean conversion setting
        $booleanSetting = new class('bool_setting', 'test_plugin', false) extends Setting {
            public function getValue(?string $key = null) {
                return $this->get($key);
            }
            
            public function setValue($value, ?string $key = null): self {
                return $this->set($value, $key);
            }
            
            protected function conversion(): Conversion {
                return Conversion::boolean();
            }
        };
        
        // Fake with database format (string)
        Setting::fakeSetting('bool_setting', 'true');
        
        $setting = new $booleanSetting('bool_setting', 'test_plugin', false);
        expect($setting->getValue())->toBe(true);
        
        // Update and verify conversion to database format
        $setting->setValue(false);
        
        $setting2 = new $booleanSetting('bool_setting', 'test_plugin', false);
        expect($setting2->getValue())->toBe(false);
    });
});

describe('WordPress function integration', function () {
    it('calls get_option when getting a value without fakes', function () {
        Wordpress::fake();
        Wordpress::mock()->shouldReceive('get_option')
            ->with('test_plugin_wp_setting', 'default_value')
            ->andReturn('db_value');
        
        $setting = new TestSetting('wp_setting', 'test_plugin', 'default_value');
        
        expect($setting->getValue())->toBe('db_value');
    });

    it('calls update_option when saving a value without fakes', function () {
        Wordpress::fake();
        Wordpress::mock()->shouldReceive('get_option')
            ->with('test_plugin_wp_save', 'default')
            ->andReturn('default');
        Wordpress::mock()->shouldReceive('update_option')
            ->with('test_plugin_wp_save', 'new_value')
            ->once();
        
        $setting = new TestSetting('wp_save', 'test_plugin', 'default');
        $setting->setValue('new_value');
        
        // The expectation is verified when the mock is torn down
    });

    it('uses correct option name format', function () {
        Wordpress::fake();
        Wordpress::mock()->shouldReceive('get_option')
            ->with('my_plugin_my_setting', 'default')
            ->andReturn('value_from_db');
        
        $setting = new TestSetting('my_setting', 'my_plugin', 'default');
        
        expect($setting->getValue())->toBe('value_from_db');
    });

    it('applies conversion when getting from database', function () {
        Wordpress::fake();
        Wordpress::mock()->shouldReceive('get_option')
            ->with('test_plugin_json_setting', null)
            ->andReturn('{"key": "value"}');
        
        $setting = new ConversionTestSetting('json_setting', 'test_plugin');
        
        expect($setting->getValue())->toBe(['key' => 'value']);
    });

    it('applies conversion when saving to database', function () {
        Wordpress::fake();
        Wordpress::mock()->shouldReceive('get_option')
            ->with('test_plugin_json_save', null)
            ->andReturn('null');
        Wordpress::mock()->shouldReceive('update_option')
            ->with('test_plugin_json_save', '{"key":"new_value"}')
            ->once();
        
        $setting = new ConversionTestSetting('json_save', 'test_plugin');
        $setting->setValue(['key' => 'new_value']);
    });
});
