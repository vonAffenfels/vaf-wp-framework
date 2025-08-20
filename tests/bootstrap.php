<?php

namespace VAF\WP\Framework\Setting;

// Mock WordPress functions in the Setting namespace for testing
if (!function_exists('VAF\WP\Framework\Setting\get_option')) {
    function get_option($name, $default = null)
    {
        return $default;
    }
}

if (!function_exists('VAF\WP\Framework\Setting\update_option')) {
    function update_option($name, $value)
    {
        return true;
    }
}
