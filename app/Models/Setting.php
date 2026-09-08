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
    public function getImagePathAttribute()
    {
        return url('images/upload') . '/';
    }
}
