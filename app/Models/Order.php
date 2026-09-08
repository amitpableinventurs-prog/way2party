<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'customer_id',
        'organization_id',
        'event_id',
        'ticket_id',
        'coupon_id',
        'quantity',
        'coupon_discount',
        'payment',
        'tax',
        'org_commission',
        'payment_type',
        'payment_status',
        'payment_token',
        'order_status',
        'org_pay_status',
        'ticket_price',
        'ticket_date',
        'checkins_count',
        'seat_details',
        'book_seats',
    ];

    protected $table = 'orders';

    protected $appends = ['review'];

    public function event()
    {
        return $this->hasOne('App\Models\Event', 'id', 'event_id');
    }
    public function ticket()
    {
        return $this->hasOne('App\Models\Ticket', 'id', 'ticket_id');
    }
    public function customer()
    {
        return $this->hasOne('App\Models\AppUser', 'id', 'customer_id');
    }
    public function organization()
    {
        return $this->hasOne('App\Models\User', 'id', 'organization_id');
    }

    public function orderchild()
    {
        return $this->hasMany('App\Models\OrderChild', 'order_id', 'id');
    }

    public function ordertax()
    {
        return $this->hasOne('App\Models\OrderTax', 'id', 'organization_id');
    }

    public function getReviewAttribute()
    {
        return Review::where('order_id',$this->attributes['id'])->first();
    }

    public function getCalendarDataAttribute()
    {
        return [
            'name' => $this->event->name,
            'description' => 'Ticket booked for ' . $this->event->name,
            'startDate' => Carbon::parse($this->ticket_date ?? $this->event->start_time)->format('Y-m-d'),
            'startTime' => Carbon::parse($this->ticket_date ?? $this->event->start_time)->format('H:i:s'),
            'endDate' => Carbon::parse($this->ticket_date ?? $this->event->end_time)->format('Y-m-d'),
            'endTime' => $this->ticket_date == null ? Carbon::parse( $this->event->end_time)->format('H:i:s') : Carbon::parse($this->ticket_date)->setTime(23, 59, 59)->format('H:i:s'),
            'timeZone' => config('app.timezone'),
            'iCalFileName' => env('APP_NAME') . '-booking-' . str_replace('#', '', $this->order_id)
        ];
    }

    /**
     * Get the check-in status string for the order.
     *
     * @return string
     */
    public function getCheckInStatusAttribute()
    {
        $totalTickets = $this->orderChild()->count();
        $checkedInTickets = $this->orderChild()->where('checkin', '>', 0)->count();

        if ($checkedInTickets === 0) {
            return  '<span style="color: red;">No Check-ins</span>';
        } elseif ($checkedInTickets === $totalTickets) {
            return '<span style="color: green;">' . $checkedInTickets . '/' . $totalTickets . '</span>';
        } else {
            return '<span style="color: blue;">' . $checkedInTickets . '/' . $totalTickets . '</span>';
        }
    }
}
