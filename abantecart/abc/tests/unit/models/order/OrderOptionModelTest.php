<?php

namespace abc\tests\unit;

use abc\models\order\OrderOption;
use Illuminate\Validation\ValidationException;

/**
 * Class OrderOptionModelTest
 */
class OrderOptionModelTest extends ATestCase
{

    protected function setUp()
    {
        //init
    }

    public function testValidator()
    {
        //validate
        $data = [
            'order_id'                => 'fail',
            'order_product_id'        => 'fail',
            'product_option_value_id' => 'fail',
            'name'                    => -0.000000000123232,
            'sku'                     => -0.000000000123232,
            'value'                   => -0.000000000123232,
            'price'                   => 'fail',
            'prefix'                  => 'fail',
            'settings'                => 'fail',
        ];

        $orderOption = new OrderOption();
        $errors = [];
        try {
            $orderOption->validate($data);
        } catch (ValidationException $e) {
            $errors = $orderOption->errors()['validation'];
            // var_Dump(array_diff(array_keys($data), array_keys($errors) ));
        }
        $this->assertEquals(9, count($errors));

        //check validation of presence in database
        $data = [
            'order_id'         => 10000000,
            'order_product_id' => 10000000,
            //check another prefix fail
            'prefix'           => -0.000000000123232,
            // fill required junk
            'name'             => 'test',
            'value'            => 'value',
        ];

        $orderOption = new OrderOption();
        $errors = [];
        try {
            $orderOption->validate($data);
        } catch (ValidationException $e) {
            $errors = $orderOption->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertEquals(3, count($errors));

        //check validation of nullables
        $data = [
            'sku'              => null,
            'settings'         => null,
            // fill required junk
            'order_id'         => 9,
            'order_product_id' => 6,
            'name'             => 'test',
            'value'            => 'value',
        ];

        $orderOption = new OrderOption();
        $errors = [];
        try {
            $orderOption->validate($data);
        } catch (ValidationException $e) {
            $errors = $orderOption->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertEquals(0, count($errors));

        //valid data
        $data = [
            'order_id'         => 9,
            'order_product_id' => 6,
            'name'             => 'test',
            'sku'              => 'test',
            'value'            => 'testvalue',
            'price'            => 1.25,
            'prefix'           => '$',
            'settings'         => ['somedata' => 'somevalue'],
        ];

        $orderOption = new OrderOption($data);
        $errors = [];
        try {
            $orderOption->validate($data);
            $orderOption->save();
        } catch (ValidationException $e) {
            $errors = $orderOption->errors()['validation'];
            var_Dump(array_diff(array_keys($data), array_keys($errors)));
            var_Dump($errors);
        }
        $this->assertEquals(0, count($errors));
        $orderOption->forceDelete();
    }
}