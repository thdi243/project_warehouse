<?php

namespace App\Http\Requests\Wcp;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class MasterBarangRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mid' => [
                'required',
                'string',
                Rule::unique('wcp_master_barang', 'mid')->ignore($this->id),
            ],
            'nama_barang' => 'required|string|max:255',
            'uom'         => 'required|string|max:50',
            'qty_pallet'  => 'required|numeric|min:0.001',
        ];
    }

    public function messages(): array
    {
        return [
            'mid.required'         => 'MID wajib diisi.',
            'mid.string'           => 'MID harus berupa teks.',
            'mid.unique'           => 'MID sudah digunakan.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'uom.required'         => 'UOM wajib diisi.',
            'qty_pallet.required'  => 'Qty Pallet wajib diisi.',
            'qty_pallet.numeric'   => 'Qty Pallet harus berupa angka.',
        ];
    }
}
