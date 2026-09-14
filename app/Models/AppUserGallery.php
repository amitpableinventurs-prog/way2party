<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppUserGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_user_id',
        'image',
    ];

    protected $appends = ['imagePath'];

    public function getImagePathAttribute()
    {
        return url('images/upload') . '/' . $this->attributes['image'];
    }

    public function appUser()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }
}
