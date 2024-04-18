<?php

namespace App\Http\Requests\Material\Validation;

use Illuminate\Foundation\Http\FormRequest;

class CodeRequest extends FormRequest
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
            // $this->get('id')
            // ?'code'=>'unique:materials,code,'.$this->get('id')
            // :'code'=>'unique:materials,code'
            'code'=>[
                'required',
                $this->get('id')
                ? 'unique:materials,code,'.$this->get('id')
                : 'unique:materials,code'
            ]
        ];
    }
}
