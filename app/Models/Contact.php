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
        'file_path',
        'file_original_name',
        'file_mime',
        'file_size',
        'source',
    ];

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }
}
