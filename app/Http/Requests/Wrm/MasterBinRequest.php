<?php

namespace App\Http\Requests\Wrm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterBinRequest extends FormRequest
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
            'loc_id' => 'required|exists:wrm_master_location,id',
            'kolom' => 'required|integer|min:1',
            'level' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'loc_id.required' => 'Location harus dipilih.',
            'loc_id.exists' => 'Location tidak valid.',
            'kolom.required' => 'Jumlah Kolom harus diisi.',
            'kolom.integer' => 'Jumlah Kolom harus berupa angka.',
            'kolom.min' => 'Jumlah Kolom minimal 1.',
            'level.required' => 'Jumlah Level harus diisi.',
            'level.integer' => 'Jumlah Level harus berupa angka.',
            'level.min' => 'Jumlah Level minimal 1.',
        ];
    }
}
