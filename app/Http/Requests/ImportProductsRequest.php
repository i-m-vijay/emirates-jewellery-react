<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'csv_file.required' => 'Please select a CSV file to import.',
            'csv_file.file' => 'The uploaded file is invalid.',
            'csv_file.mimes' => 'The file must be a CSV file (comma-separated values).',
            'csv_file.max' => 'The file must not exceed 10MB.',
        ];
    }
}
