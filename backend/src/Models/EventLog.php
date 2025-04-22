<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    protected $fillable = ['event_type', 'payload'];
    protected $casts = [
        'payload' => 'array'
    ];
    public $timestamps = true;
    const UPDATED_AT = null; // We only need created_at
}
