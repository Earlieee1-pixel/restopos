<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

// Validation kung mag-update sa order (e.g. pag-serve, i-record ang bayad)
class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cashier, manager, ug admin lang pwede
        return in_array(auth()->user()?->role, ['cashier', 'manager', 'admin']);
    }

    public function rules(): array
    {
        return [
            // Gikinahanglan kung mag-serve — para ma-compute ang sukli
            'amount_tendered' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
