<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = [
        'exhibition_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'note',
        'business_card_path',
        'source',
    ];

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }
}