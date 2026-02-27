<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exhibition extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'date',
        'start_date',
        'end_date',
        'company',
        'public_token',
    ];

    protected $casts = [
        'date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected function displayDate(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->start_date && $this->end_date) {
                    return $this->start_date->format('d/m/Y').' - '.$this->end_date->format('d/m/Y');
                }

                if ($this->date) {
                    return $this->date->format('d/m/Y');
                }

                return $this->start_date?->format('d/m/Y') ?? '-';
            }
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
