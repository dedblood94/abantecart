<?php

namespace abc\models\base;

use abc\models\BaseModel;

/**
 * Class OrderDatum
 *
 * @property int $order_id
 * @property int $type_id
 * @property string $data
 * @property \Carbon\Carbon $date_added
 * @property \Carbon\Carbon $date_modified
 *
 * @property Order $order
 * @property OrderDataType $order_data_type
 *
 * @package abc\models
 */
class OrderDatum extends BaseModel
{
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'order_id' => 'int',
        'type_id'  => 'int',
    ];

    protected $dates = [
        'date_added',
        'date_modified',
    ];

    protected $fillable = [
        'data',
        'date_added',
        'date_modified',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function order_data_type()
    {
        return $this->belongsTo(OrderDataType::class, 'type_id');
    }
}
