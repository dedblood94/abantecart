<?php

namespace abc\tests\unit;

use abc\models\catalog\Category;
use abc\models\catalog\Product;
use abc\models\catalog\ProductDiscount;
use abc\models\catalog\ProductOption;
use abc\models\catalog\ProductOptionDescription;
use abc\models\catalog\ProductOptionValue;
use abc\models\catalog\ProductOptionValueDescription;
use abc\models\catalog\ProductSpecial;
use abc\models\catalog\ProductTag;
use abc\models\customer\Address;
use abc\models\customer\Customer;
use abc\models\customer\CustomerNotification;
use abc\models\customer\CustomerTransaction;
use abc\models\order\Order;
use abc\models\order\OrderDatum;
use abc\models\order\OrderOption;
use abc\models\order\OrderProduct;
use abc\models\order\OrderStatus;
use abc\models\order\OrderStatusDescription;
use abc\models\order\OrderTotal;

/**
 * Class TouchesTest
 */
class TouchesTest extends ATestCase
{

    public function testCustomerTouches()
    {
        $address = Address::find(1);
        $now = time();
        $address->touch();
        $customer = Customer::find($address->customer_id);
        $this->assertEquals($now, $customer->date_modified->timestamp);

        sleep(2);
        $now = time();
        CustomerTransaction::create(
            [
                'customer_id'      => $customer->customer_id,
                'created_by'       => 1,
                'section'          => 1,
                'credit'           => 0.0001,
                'transaction_type' => 'unit-test',
            ]);
        $customer = Customer::find($address->customer_id);
        $this->assertEquals($now, $customer->date_modified->timestamp);

        sleep(2);
        $now = time();
        CustomerNotification::create(
            [
                'customer_id' => $customer->customer_id,
                'sendpoint'   => 'some-test-sendpoint',
                'protocol'    => 'sms',
                'status'      => 0,
            ]);
        $customer = Customer::find($address->customer_id);
        $this->assertEquals($now, $customer->date_modified->timestamp);
    }

    public function testOrderTouches()
    {
        /** @var OrderOption $orderOption */
        $orderOption = OrderOption::where('order_product_id', '>', 0)->first();
        $now = time();
        $orderOption->touch();
        $orderProduct = OrderProduct::find($orderOption->order_product_id);
        $this->assertEquals($now, $orderProduct->date_modified->timestamp);

        $order = Order::find($orderOption->order_id);
        $this->assertEquals($now, $order->date_modified->timestamp);

        sleep(2);
        $now = time();
        OrderDatum::create(
            [
                'order_id' => $orderOption->order_id,
                'type_id'  => 1,
                'data'     => 'unittest@test.test',
            ]
        );
        $order = Order::find($orderOption->order_id);
        $this->assertEquals($now, $order->date_modified->timestamp);

        //order total
        sleep(2);
        $orderTotal = OrderTotal::where('order_id', '=', $orderOption->order_id)->first();
        $now = time();
        $orderTotal->touch();

        $order = Order::find($orderOption->order_id);
        $this->assertEquals($now, $order->date_modified->timestamp);

        sleep(2);
        /** @var OrderStatusDescription $orderStatusDescription */
        $orderStatusDescription = OrderStatusDescription::first();
        $now = time();
        $orderStatusDescription->touch();
        $orderStatus = OrderStatus::find($orderStatusDescription->order_status_id);
        $this->assertEquals($now, $orderStatus->date_modified->timestamp);

    }

    public function testProductTouches()
    {
        /** @var ProductOptionValueDescription $optionValueDescription */
        $optionValueDescription = ProductOptionValueDescription::first();
        $now = time();
        $optionValueDescription->touch();
        $optionValue = ProductOptionValue::find($optionValueDescription->product_option_value_id);
        $this->assertEquals($now, $optionValue->date_modified->timestamp);

        $option = ProductOption::find($optionValue->product_option_id);
        $this->assertEquals($now, $option->date_modified->timestamp);

        $product = Product::with('categories')->find($optionValueDescription->product_id);
        $this->assertEquals($now, $product->date_modified->timestamp);

        $category = $product->categories->first();
        $this->assertEquals($now, $category->date_modified->timestamp);

        sleep(2);
        /** @var ProductOptionDescription $optionDescription */
        $optionDescription = ProductOptionDescription::first();
        $now = time();
        $optionDescription->touch();

        $option = ProductOption::find($optionDescription->product_option_id);
        $this->assertEquals($now, $option->date_modified->timestamp);

        $product = Product::with('categories')->find($optionDescription->product_id);
        $this->assertEquals($now, $product->date_modified->timestamp);

        sleep(2);
        /** @var ProductTag $tag */
        $tag = ProductTag::first();
        $now = time();
        $newTag = new ProductTag(
            [
                'product_id'  => $tag->product_id,
                'language_id' => $tag->language_id,
                'tag'         => $now,
            ]
        );
        $newTag->save();
        $product = Product::find($tag->product_id);
        $this->assertEquals($now, $product->date_modified->timestamp);

        sleep(2);
        /** @var ProductSpecial $special */
        $special = ProductSpecial::first();
        $now = time();
        $special->touch();
        $product = Product::find($special->product_id);
        $this->assertEquals($now, $product->date_modified->timestamp);
        sleep(2);
        /** @var ProductDiscount $discount */
        $discount = ProductDiscount::first();
        $now = time();
        $discount->touch();
        $product = Product::find($discount->product_id);
        $this->assertEquals($now, $product->date_modified->timestamp);

    }

}