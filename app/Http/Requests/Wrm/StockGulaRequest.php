<?php

namespace App\Http\Requests\Wrm;

use Illuminate\Foundation\Http\FormRequest;

class StockGulaRequest extends FormRequest
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
            'barang_id' => 'required|exists:wrm_master_barang,id',
            'no_spb'    => 'required|integer',
            'qty'       => 'required|integer|min:1',
            'group'     => 'nullable|string|max:255',
            'supplier'  => 'required|string|max:255',
            'status'    => 'required|string|max:50',
            'loc_id'    => 'required|exists:wrm_master_location,id',
            'catatan'   => 'nullable|string',
            'incoming_date' => 'nullable|date'
        ];
    }
}
