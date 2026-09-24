<?php

namespace App\Http\Requests\Kempu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterKempuRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $kempuId = $this->route('id') ?? $this->id;

        return [
            'id_kempu'   => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('kempu_master', 'id_kempu')->ignore($kempuId),
            ],
            'gr_date'    => 'nullable|date',
            'no_spb'     => 'nullable|string|max:100',
            'rfid'       => 'nullable|string|max:100',
            'status'     => 'nullable|string|in:active,in_use,maintenance,damaged,scrap',
            'keterangan' => 'nullable|string|max:1000',
            'is_bulk'    => 'nullable|boolean',
            'qty'        => 'nullable|integer|min:1|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'id_kempu.unique' => 'ID Kempu sudah digunakan, gunakan ID lain.',
            'gr_date.date'    => 'Format Tanggal GR tidak valid.',
        ];
    }
}
