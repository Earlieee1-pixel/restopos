<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

// Validation para sa pag-create ug order
class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cashier ug manager lang pwede
        return in_array(auth()->user()?->role, ['cashier', 'manager', 'admin']);
    }

    public function rules(): array
    {
        return [
            'table_id'              => ['nullable', 'exists:tables,id'],
            'order_type'            => ['required', 'in:dine-in,takeout'],
            'discount'              => ['nullable', 'numeric', 'min:0'],
            'amount_tendered'       => ['nullable', 'numeric', 'min:0'],
            'notes'                 => ['nullable', 'string'],
            // Kinahanglan may sulod nga items
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
            'items.*.notes'         => ['nullable', 'string'],
        ];
    }
}
