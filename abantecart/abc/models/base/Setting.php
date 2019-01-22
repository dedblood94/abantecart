<?php

namespace abc\models\base;

use abc\models\BaseModel;

/**
 * Class Setting
 *
 * @property int $setting_id
 * @property int $store_id
 * @property string $group
 * @property string $key
 * @property string $value
 * @property \Carbon\Carbon $date_added
 * @property \Carbon\Carbon $date_modified
 *
 * @property Store $store
 *
 * @package abc\models
 */
class Setting extends BaseModel
{
    public $timestamps = false;

    protected $casts = [
        'store_id' => 'int',
    ];

    protected $dates = [
        'date_added',
        'date_modified',
    ];

    protected $fillable = [
        'value',
        'date_added',
        'date_modified',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
