<?php

namespace abc\models;

use abc\models\AModelBase;

/**
 * Class TaxClass
 *
 * @property int                                      $tax_class_id
 * @property \Carbon\Carbon                           $date_added
 * @property \Carbon\Carbon                           $date_modified
 *
 * @property \Illuminate\Database\Eloquent\Collection $tax_class_descriptions
 * @property \Illuminate\Database\Eloquent\Collection $tax_rates
 *
 * @package abc\models
 */
class TaxClass extends AModelBase
{
    protected $primaryKey = 'tax_class_id';
    public $timestamps = false;

    protected $dates = [
        'date_added',
        'date_modified',
    ];

    protected $fillable = [
        'date_added',
        'date_modified',
    ];

    public function tax_class_descriptions()
    {
        return $this->hasMany(TaxClassDescription::class, 'tax_class_id');
    }

    public function tax_rates()
    {
        return $this->hasMany(TaxRate::class, 'tax_class_id');
    }
}
