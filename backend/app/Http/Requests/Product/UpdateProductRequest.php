<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

// Validation para sa pag-edit sa produkto
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Manager ug admin lang pwede mag-edit
        return in_array(auth()->user()?->role, ['manager', 'admin']);
    }

    public function rules(): array
    {
        return [
            'name'         => ['sometimes', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'price'        => ['sometimes', 'numeric', 'min:0'],
            'category_id'  => ['sometimes', 'exists:categories,id'],
            'image'        => ['nullable', 'string', 'max:500'],
            'is_available' => ['boolean'],
        ];
    }
}
