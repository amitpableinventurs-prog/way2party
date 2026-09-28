<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $table = 'tags';

    public function getSlugAttribute()
    {
        return Str::slug($this->name);
    }

    // Tag page, scoped to a city when one is given: /city/chennai/tag/dj-night
    public function url(?City $city = null)
    {
        return $city
            ? url('/city/' . $city->slug . '/tag/' . $this->slug)
            : url('/tag/' . $this->slug);
    }

    // Slug is derived from the name (no column), so match in PHP — the table is small.
    public static function findBySlug($slug)
    {
        return static::all()->first(fn ($tag) => $tag->slug === Str::slug($slug));
    }
}
