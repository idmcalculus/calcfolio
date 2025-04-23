<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password_hash', // Store the hash, not the plain password
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash', // Never expose the hash
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // No password casting needed here as we handle hashing manually
    ];

    // Disable timestamps if 'created_at' and 'updated_at' are not managed by Eloquent
    // public $timestamps = false; // Keep true if using timestamps from migration/schema
}
