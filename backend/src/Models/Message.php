<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    // Added 'is_read' to fillable attributes
    protected $fillable = ['name', 'email', 'subject', 'message', 'message_id', 'status', 'is_read'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_read' => 'boolean', // Cast is_read to boolean
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_BOUNCED = 'bounced';
    const STATUS_OPENED = 'opened';
    const STATUS_CLICKED = 'clicked';
    const STATUS_COMPLAINED = 'complained';
}
