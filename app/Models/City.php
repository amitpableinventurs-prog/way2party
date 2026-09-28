<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'status',
    ];

    protected $table = 'city';

    public function getSlugAttribute()
    {
        return Str::slug($this->name);
    }

    public function getUrlAttribute()
    {
        return url('/city/' . $this->slug);
    }

    // Slug is derived from the name (no column), so match in PHP — the city table is small.
    public static function findBySlug($slug)
    {
        return static::all()->first(fn ($city) => $city->slug === Str::slug($slug));
    }
}
