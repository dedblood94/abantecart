<?php

namespace abc\models\base;

use abc\models\AModelBase;
use abc\models\BannerStat;

/**
 * Class Banner
 *
 * @property int $banner_id
 * @property int $status
 * @property int $banner_type
 * @property string $banner_group_name
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property bool $blank
 * @property string $target_url
 * @property int $sort_order
 * @property \Carbon\Carbon $date_added
 * @property \Carbon\Carbon $date_modified
 *
 * @property \Illuminate\Database\Eloquent\Collection $banner_descriptions
 * @property \Illuminate\Database\Eloquent\Collection $banner_stats
 *
 * @package abc\models
 */
class Banner extends AModelBase
{
    protected $primaryKey = 'banner_id';
    public $timestamps = false;

    protected $casts = [
        'status'      => 'int',
        'banner_type' => 'int',
        'blank'       => 'bool',
        'sort_order'  => 'int',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'date_added',
        'date_modified',
    ];

    protected $fillable = [
        'status',
        'banner_type',
        'banner_group_name',
        'start_date',
        'end_date',
        'blank',
        'target_url',
        'sort_order',
        'date_added',
        'date_modified',
    ];

    public function banner_descriptions()
    {
        return $this->hasMany(BannerDescription::class, 'banner_id');
    }

    public function banner_stats()
    {
        return $this->hasMany(BannerStat::class, 'banner_id');
    }
}
