<?php

namespace VAF\WP\FrameworkTests\Unit;

use VAF\WP\Framework\Setting\Conversion;
use VAF\WP\FrameworkTests\TestCase;

class BooleanConversionTest extends TestCase
{

    /**
     * @test
     */
    public function should_convert_string_true_in_db_to_true_boolean()
    {
        $result = (Conversion::boolean()->fromDb)('true');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function should_convert_string_false_in_db_to_false_boolean()
    {
        $result = (Conversion::boolean()->fromDb)('false');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function should_convert_string_inactive_in_db_to_false_boolean()
    {
        $result = (Conversion::boolean()->fromDb)('inactive');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function should_convert_string_active_in_db_to_true_boolean()
    {
        $result = (Conversion::boolean()->fromDb)('active');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function should_convert_string_1_in_db_to_true_boolean()
    {
        $result = (Conversion::boolean()->fromDb)('1');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function should_convert_string_0_in_db_to_false_boolean()
    {
        $result = (Conversion::boolean()->fromDb)('0');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function should_convert_input_string_active_to_true()
    {
        $result = (Conversion::boolean()->fromInput)('active');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function should_convert_input_string_inactive_to_false()
    {
        $result = (Conversion::boolean()->fromInput)('inactive');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function should_convert_input_string_true_to_true()
    {
        $result = (Conversion::boolean()->fromInput)('true');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function should_convert_input_string_false_to_false()
    {
        $result = (Conversion::boolean()->fromInput)('false');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function should_convert_true_boolean_value_to_true_string_in_db()
    {
        $result = (Conversion::boolean()->toDb)(true);

        $this->assertEquals('true', $result);
    }

    /**
     * @test
     */
    public function should_convert_false_boolean_value_to_false_string_in_db()
    {
        $result = (Conversion::boolean()->toDb)(false);

        $this->assertEquals('false', $result);
    }
}
