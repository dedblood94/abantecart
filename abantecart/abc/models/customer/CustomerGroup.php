<?php

namespace abc\models\customer;

use abc\models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CustomerGroup
 *
 * @property int $customer_group_id
 * @property string $name
 * @property bool $tax_exempt
 *
 * @method static CustomerGroup find(int $customer_group_id) CustomerGroup
 * @package abc\models
 */

class CustomerGroup extends BaseModel
{
    use SoftDeletes;
    protected $primaryKey = 'customer_group_id';

    protected $casts = [
        'tax_exempt' => 'bool',
    ];
    protected $dates = [
        'date_added',
        'date_modified',

    ];

    protected $fillable = [
        'name',
        'tax_exempt',
    ];
    protected $rules= [

        'name'=>[
            'checks'=>[
                'string',
                'between:2,64'
            ],
            'messages'=>[
                'language_key'=>'error_name',
                'language_block'=>'admin/create',
                'default_text'=>'Name group must be between 2 and 64 characters!',
                'section'=> '??',
                ]
        ],
        'tax_exempt' =>[
            'checks'=>[
                'boolean',
                'sometimes',
            ],
            'messages'=>[
                '*'=>['default_text'=>'Tax_exempt is not boolean']
            ]
        ],

    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_group_id');
    }
}
