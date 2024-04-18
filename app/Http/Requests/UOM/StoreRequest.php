<?php

namespace App\Http\Requests\UOM;

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
                $this->route()->uom
                    ? 'unique:uom,code,'.$this->route()->uom
                    : 'unique:uom,code'
                ],
            'description'=> [
                'required',
                'string',
                $this->route()->uom
                    ? 'unique:uom,description,'.$this->route()->uom
                    : 'unique:uom,description'
                    ],
      
        ];
    }
}
