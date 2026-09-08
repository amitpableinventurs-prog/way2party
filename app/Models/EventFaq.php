<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventFaq extends Model
{
    use HasFactory;

    protected $table = 'event_faqs';
    protected $guarded = [];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
