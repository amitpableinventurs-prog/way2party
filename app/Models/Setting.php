<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'general_settng';
    protected $appends = ['imagePath'];
    protected $guarded  = [];
    protected $hidden  = ['openai_key'];

    /** Request-scoped copy of the single settings row (id 1). */
    protected static $current;

    /**
     * The settings row, fetched at most once per request. Layouts, view composers and
     * helpers used to call Setting::find(1) 15+ times per page — one query each.
     */
    public static function current()
    {
        if (static::$current === null) {
            static::$current = static::find(1) ?: false;
        }
        return static::$current ?: null;
    }

    protected static function booted()
    {
        static::saved(function () {
            static::$current = null;
        });
    }

    public function getImagePathAttribute()
    {
        return url('images/upload') . '/';
    }
}
