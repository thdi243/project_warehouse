<?php

namespace App\Http\Requests\Wrm;

use Illuminate\Foundation\Http\FormRequest;

class SubmitOutboundRequest extends FormRequest
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
            'no_reservasi' => 'required|string|max:100',
            'shift' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:wrm_stock_inbound_details,id',
            'items.*.qty' => 'required|numeric|min:1',
            'catatan' => 'nullable|string|max:500',
            'qty_request' => 'nullable|numeric|min:1',
            'checklist_kondisi' => 'nullable|array'
        ];
    }
}
