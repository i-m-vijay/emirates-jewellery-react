<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:150',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a category.',
            'category_id.exists'   => 'The selected category does not exist.',
            'name.required'        => 'Subcategory name is required.',
            'image.image'          => 'The file must be a valid image.',
            'image.mimes'          => 'Image must be JPG, PNG, or WEBP.',
            'image.max'            => 'Image must not exceed 2MB.',
        ];
    }
}
