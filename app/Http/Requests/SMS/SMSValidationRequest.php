<?php

namespace App\Http\Requests\SMS;

use Illuminate\Foundation\Http\FormRequest;

class SMSValidationRequest extends FormRequest
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
            "results.*.from"=>[
                'required',
                'exists:users_store,mobile_no'
            ],
            "results.*.cleanText"=>'required'   
        ];
    }
}
