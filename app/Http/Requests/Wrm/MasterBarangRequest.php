<?php

namespace App\Http\Requests\Wrm;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class MasterBarangRequest extends FormRequest
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
            'mid' => [
                'required',
                'integer',
                Rule::unique('wrm_master_barang', 'mid')->ignore($this->id),
            ],
            'nama_barang'  => 'required|string|max:255',
            'uom'          => 'required|string|max:50',
            'loc_id'        => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'mid.required'        => 'MID wajib diisi.',
            'mid.integer'         => 'MID harus berupa angka.',
            'mid.unique'          => 'MID sudah digunakan.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'uom.required'        => 'UOM wajib diisi.',
            's_loc.required'      => 'S Loc wajib diisi.',
        ];
    }
}
