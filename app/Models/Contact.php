<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function scopeSearch(Builder $query, string $q): Builder
    {
        return $query->where(function (Builder $sub) use ($q) {
            $sub->where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('company', 'like', "%{$q}%")
                ->orWhere('note', 'like', "%{$q}%");
        });
    }

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }
}
