<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function plans()
    {
        return $this->belongsToMany(MembershipPlan::class, 'membership_plan_features');
    }
}
