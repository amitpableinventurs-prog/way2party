<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'user_id',
        'type',
        'address',
        'category_id',
        'city_id',
        'start_time',
        'end_time',
        'image',
        'gallery',
        'people',
        'lat',
        'lang',
        'description',
        'security',
        'status',
        'is_featured',
        'featured_order',
        'visibility',
        'cancellation_allowed',
        'cancellation_charges',
        'event_status',
        'is_deleted',
        'scanner_id',
        'tags',
        'url',
    ];

    protected $table = 'events';
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_featured' => 'boolean',
        'featured_order' => 'integer',
        'cancellation_allowed' => 'boolean',
        'cancellation_charges' => 'decimal:2',
    ];
    protected $appends = ['imagePath', 'rate', 'totalTickets', 'soldTickets'];

    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }

    public function city()
    {
        return $this->hasOne('App\Models\City', 'id', 'city_id');
    }

    public function organization()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
    public function ticket()
    {
        return $this->hasMany('App\Models\Ticket', 'event_id', 'id');
    }

    public function faqs()
    {
        return $this->hasMany('App\Models\EventFaq', 'event_id', 'id');
    }

    public function getImagePathAttribute()
    {
        return url('images/upload') . '/';
    }

    /**
     * Get total of Tickets Count
     * @return int
     */
    public function getTotalTicketsAttribute()
    {
        $timezone = Setting::find(1)->timezone;
        $date = Carbon::now($timezone);
        return intval(Ticket::where([['event_id', $this->attributes['id']], ['is_deleted', 0], ['status', 1], ['end_time', '>=', $date->format('Y-m-d H:i:s')], ['start_time', '<=', $date->format('Y-m-d H:i:s')]])->sum('quantity'));
    }

    /**
     * Get total of Sold Tickets Count
     * @return int
     */
    public function getSoldTicketsAttribute()
    {
        // (new AppHelper)->eventStatusChange();
        return  intval(Order::where('event_id', $this->attributes['id'])->sum('quantity'));
        // return  Order::where('event_id', $this->attributes['id'])->sum('quantity');
    }

    public function getRateAttribute()
    {
        $review =  Review::where('event_id', $this->attributes['id'])->get(['rate']);
        if (count($review) > 0) {
            $totalRate = 0;
            foreach ($review as $r) {
                $totalRate = $totalRate + $r->rate;
            }
            return  round($totalRate / count($review));
        } else {
            return 0;
        }
    }

    public function scopeDurationData($query, $start, $end)
    {
        $data =  $query->whereBetween('start_time', [$start,  $end]);
        return $data;
    }

    /**
     * Live, upcoming events that an admin/organizer has marked as featured.
     */
    public function scopeFeatured($query)
    {
        return $query->where([['is_featured', 1], ['status', 1], ['is_deleted', 0], ['event_status', 'Pending']]);
    }

    /**
     * Highest priority first (admin-set), then soonest starting event.
     */
    public function scopeOrderByFeatured($query)
    {
        return $query->orderByDesc('featured_order')->orderBy('start_time', 'asc');
    }

    /**
     * Restricts a listing query to events the given customer (AppUser, or
     * null for a guest) is allowed to see.
     *
     * 'everyone' (or unset, for legacy rows) is always visible. 'hidden' and
     * 'members_only' are excluded from every public listing for now -
     * 'members_only' enforcement is deferred until the membership system
     * ships; treating it as hidden in the meantime is the safe default.
     * 'previously_attended' is visible only to a customer with a past order
     * for any past event by this event's organizer.
     */
    public function scopeVisibleTo($query, $appUser = null)
    {
        $appUserId = $appUser->id ?? null;
        return $query->where(function ($q) use ($appUserId) {
            $q->where('visibility', 'everyone')->orWhereNull('visibility');
            if ($appUserId) {
                $q->orWhere(function ($q2) use ($appUserId) {
                    $q2->where('visibility', 'previously_attended')
                        ->whereIn('user_id', function ($sub) use ($appUserId) {
                            $sub->select('events.user_id')
                                ->from('orders')
                                ->join('events', 'orders.event_id', '=', 'events.id')
                                ->where('orders.customer_id', $appUserId)
                                ->where('events.end_time', '<', now());
                        });
                });
            }
        });
    }

    /**
     * Get Available Tickets Count, based on ordered tickets
     * @return int
     */
    public function getAvailableTicketsAttribute()
    {
        return $this->totalTickets - $this->soldTickets;
    }

    /**
     * Get Auto Generated Tag.
     * Tags: Sold Out, Almost Full, Sales ending soon
     * Intentionally returning just one tag to fit in UI, but it can be multiple.
     * @return String
     */
    public function getAutoGeneratedTagAttribute()
    {
        if($this->availableTickets <= 0){
            return "sold out";
        }

        if($this->totalTickets - $this->soldTickets <= 10){
            return "almost full";
        }

        // ticket whose end date is closing at the last.
        $ticket = Ticket::where('event_id', $this->id)->orderBy('end_time', 'desc')->first();
        if($ticket->end_time->diffInDays(now()) <= 3){
            return "sales ending soon";
        }
        return '';
    }
}
