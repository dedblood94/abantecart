<?php
namespace abc\tests\unit;

use abc\models\customer\CustomerGroup;
use Illuminate\Validation\ValidationException;


/**
 * Class CustomerGroupTest
 */
class CustomerGroupTest extends ATestCase
{

    public function testValidator()
    {

        $customer = new CustomerGroup(
            [
                'name' => '',
                'tax_exempt' => '3',
            ]
        );
        $errors = [];
        try {
            $customer->validate();
        } catch (ValidationException $e) {
            $errors = $customer->errors()['validation'];
        }
        var_dump($errors);

        $this->assertEquals(2, count($errors));


        $customer = new CustomerGroup(
            [
                'name' => 'Gregor',
                'tax_exempt' => '0',
            ]
        );
        $errors = [];
        try {
            $customer->validate();
        } catch (ValidationException $e) {
            $errors = $customer->errors()['validation'];
        }

        $this->assertEquals(0, count($errors));

    }
}