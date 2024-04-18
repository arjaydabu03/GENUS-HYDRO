<?php

namespace App\Http\Requests\User\Validation;

use Illuminate\Foundation\Http\FormRequest;

class NameRequest extends FormRequest
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
            'name'=>[
                'required',
                $this->get('id')
                ?'unique:users,account_name,'. $this->get('id')
                :'unique:users,account_name'
                ]
        ];
    }
    public function messages(){
        return [
            'unique'=>'This name is already registered.'
        ];
    }
}
