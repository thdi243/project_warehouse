<?php

namespace App\Http\Requests\Wrm;

use Illuminate\Foundation\Http\FormRequest;

class StockGulaUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'loc_id' => 'required|array',
            // 'loc_id.*' => 'required|exists:wrm_master_location,id'
            'loc_id' => 'array',
            // 'loc_id.*' => 'exists:wrm_master_location,id'
        ];
    }
}
