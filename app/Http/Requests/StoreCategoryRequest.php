<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'category_name' => 'required|string|max:100|unique:categories,category_name,' . $categoryId,
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'Category name is required.',
            'category_name.unique'   => 'This category already exists.',
            'category_name.max'      => 'Category name may not exceed 100 characters.',
        ];
    }
}
