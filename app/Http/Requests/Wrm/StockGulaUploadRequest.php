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
            'supplier' => 'required|string|max:255',
            'pallet' => 'required|string|max:255',
            'incoming_date' => 'required|date',
            'loc_id' => 'required|array',
            'loc_id.*' => 'required|exists:wrm_master_bin,id',
            'status' => 'required|array',
            'status.*' => 'required|in:UNREST,QI,BLOCKED'
        ];
    }
}
