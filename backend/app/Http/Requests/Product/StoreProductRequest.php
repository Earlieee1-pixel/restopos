<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

// Validation para sa pag-create ug bag-ong produkto
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Manager ug admin lang pwede mag-add ug produkto
        return in_array(auth()->user()?->role, ['manager', 'admin']);
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'price'        => ['required', 'numeric', 'min:0'],
            'category_id'  => ['required', 'exists:categories,id'],
            'image'        => ['nullable', 'string', 'max:500'],
            'is_available' => ['boolean'],
        ];
    }
}
