<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'price',
        'duration_days',
        'image',
        'description',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'price' => 'decimal:2',
        'status' => 'boolean',
    ];

    protected $appends = ['imagePath'];

    public function getImagePathAttribute()
    {
        return $this->attributes['image'] ? url('images/upload') . '/' . $this->attributes['image'] : null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function features()
    {
        return $this->belongsToMany(MembershipFeature::class, 'membership_plan_features');
    }
}
