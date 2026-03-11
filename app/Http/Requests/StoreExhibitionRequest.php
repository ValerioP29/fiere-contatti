<?php

namespace App\Http\Requests;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

class StoreExhibitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && app(TenantContext::class)->currentFromRequest($this) !== null;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'date_mode'  => ['nullable', 'string', 'in:single,range'],
            'date'       => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'company'    => ['nullable', 'string', 'max:255'],
            'note'       => ['nullable', 'string', 'max:5000'],
        ];
    }
}
