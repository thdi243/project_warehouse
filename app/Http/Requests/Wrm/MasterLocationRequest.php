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
            'bin'       => 'required|string',
            's_loc'     => 'required|string',
            'plant' => [
                'required',
                Rule::unique('wrm_master_location')
                    ->where(function ($query) {
                        return $query
                            ->where('gudang', $this->gudang)
                            ->where('bin', $this->bin)
                            ->where('s_loc', $this->s_loc);
                    })
            ],
        ];
    }

    public function messages()
    {
        return [
            'plant.unique' => 'Kombinasi Gudang, Bin, S_Loc dan Plant sudah ada.'
        ];
    }
}
