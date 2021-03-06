<?php

namespace abc\models\locale;

use abc\models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class WeightClassDescription
 *
 * @property int $weight_class_id
 * @property int $language_id
 * @property string $title
 * @property string $unit
 *
 * @property WeightClass $weight_class
 * @property Language $language
 *
 * @package abc\models
 */
class WeightClassDescription extends BaseModel
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'weight_class_id',
        'language_id',
    ];

    public $timestamps = false;

    protected $casts = [
        'weight_class_id' => 'int',
        'language_id'     => 'int',
    ];

    protected $fillable = [
        'title',
        'unit',
    ];
    protected $rules=[
        'title'=>[
            'checks'=>[
                'string',
                'between:2,32',
                'required',
                'sometimes'
            ],
            'messages'=>[
                'language_key'=> 'error_title',
                'language_block'=>'localisation/weight_class',
                'default_text'=>'Title must be between 2 and 32 characters!',
                'section'=>'admin'
            ]
        ],
        'unit'=>[
            'checks'=>[
               'string',
               'max:4',
                'required',
                'sometimes'
            ],
            'messages'=>[
                'language_key'=> 'error_unit',
                'language_block'=>'localisation/weight_class',
                'default_text'=>'Unit must be between 0 and 4 characters!',
                'section'=>'admin'
            ]
        ]
    ];

    public function weight_class()
    {
        return $this->belongsTo(WeightClass::class, 'weight_class_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
