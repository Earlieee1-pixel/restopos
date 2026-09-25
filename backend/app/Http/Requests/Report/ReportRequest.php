<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

// Validation para sa tanan report endpoints
class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()?->role, ['manager', 'admin']);
    }

    public function rules(): array
    {
        return [
            // Optional date filter — kinahanglan valid date format
            'date'  => ['nullable', 'date_format:Y-m-d'],
            // Optional month filter — kinahanglan YYYY-MM format
            'month' => ['nullable', 'date_format:Y-m'],
            // Optional limit — integer, 1-100
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.date_format'  => 'Date must be in YYYY-MM-DD format.',
            'month.date_format' => 'Month must be in YYYY-MM format.',
            'limit.integer'     => 'Limit must be a number.',
            'limit.min'         => 'Limit must be at least 1.',
            'limit.max'         => 'Limit cannot exceed 100.',
        ];
    }
}
