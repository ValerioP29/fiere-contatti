<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exhibition extends Model
{
    protected $fillable = [
        'name', 'date', 'company', 'public_token'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}