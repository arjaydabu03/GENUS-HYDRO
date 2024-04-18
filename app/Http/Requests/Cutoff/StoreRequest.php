<?php

namespace App\Http\Requests\Cutoff;

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
            'name'=> [
                'required',
                'string',
                $this->route()->cut_off
                    ? 'unique:cut_off,name,'.$this->route()->cut_off
                    : 'unique:cut_off,name'
            ],
            'time'=> [
                'required',
                'string',
                $this->route()->cut_off
                    ? 'unique:cut_off,time,'.$this->route()->cut_off
                    : 'unique:cut_off,time'
            ],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // $validator->errors()->add("custom", "STOP!");
            // $validator->errors()->add("custom", $this->route()->id);
        });
    }
}
