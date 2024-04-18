<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'code'=> [
                'required',
                'string',
                $this->route()->warehouse
                    ? 'unique:warehouse,code,'.$this->route()->warehouse
                    : 'unique:warehouse,code'
            ],
            'name'=>'required'
        ];
    }
}
