<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_name',
        'first_name',
        'last_name',
        'email',
        'password',
        'status',
        'phone',
        'image',
        'device_token',
        'org_id',
        'bio',
        'country',
        'language',
        'is_verify',
        'provider',
        'provider_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $appends = ['followers', 'imagePath'];

    public function getFollowersAttribute()
    {
        $appuser = AppUser::get();
        $followers = array();
        foreach ($appuser as $user) {
            if (in_array($this->attributes['id'], array_filter(explode(',', $user->following)))) {
                array_push($followers, $user->id);
            }
        }
        return $followers;
    }

    public function getImagePathAttribute()
    {
        return url('images/upload') . '/' . $this->attributes['image'];
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the customers of the Organizer through the orders.
     * `users` table is the Organizer's table with id as primary key.
     * `appusers` table is the Customers table with id as primary key.
     * `orders` table is the orders table with customer_id as foreign key which is a primary key in the appusers table
     * & organization_id as foreign key which is a primary key in the users table.
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function customers()
    {
        return $this->hasManyThrough(AppUser::class, Order::class, 'organization_id', 'id', 'id', 'customer_id');

    }

    /**
     * Get the taxes of the Organizer.
     */
    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

}
