<?php

namespace App\Http\Requests\Wrm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterLocationRequest extends FormRequest
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
            'gudang'    => 'required|string',
            's_loc'     => 'required|string',
            'zona'     => 'required|string',
            'plant' => [
                'required',
                Rule::unique('wrm_master_location')
                    ->where(function ($query) {
                        return $query
                            ->where('s_loc', $this->s_loc)
                            ->where('gudang', $this->gudang)
                            ->where('zona', $this->zona);
                    })
            ],
        ];
    }

    public function messages()
    {
        return [
            'plant.unique' => 'Kombinasi Plant, Gudang, S_Loc, Zona, dan Bin sudah ada.'
        ];
    }
}
