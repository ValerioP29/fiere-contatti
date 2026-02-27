<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExhibitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date' => ['nullable', 'date', 'required_without:start_date'],
            'start_date' => ['nullable', 'date', 'required_without:date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'company' => ['nullable', 'string', 'max:255'],
        ];
    }
}
