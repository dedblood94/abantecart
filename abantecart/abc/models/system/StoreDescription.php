<?php

namespace abc\models\system;

use abc\models\BaseModel;
use abc\models\locale\Language;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class StoreDescription
 *
 * @property int $store_id
 * @property int $language_id
 * @property string $description
 * @property string $title
 * @property string $meta_description
 * @property string $meta_keywords
 *
 * @property Store $store
 * @property Language $language
 *
 * @package abc\models
 */
class StoreDescription extends BaseModel
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'store_id',
        'language_id',
    ];
    public $timestamps = false;

    protected $casts = [
        'store_id'    => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'description',
        'title',
        'meta_description',
        'meta_keywords',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
