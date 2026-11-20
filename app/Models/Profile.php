<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'phone_number',
        'bio',
        'avatar_url',
    ];

    /**
     * Get the user that owns the profile (Inverse One-to-One).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

