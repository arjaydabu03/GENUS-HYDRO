<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class CategoryRequest extends FormRequest
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
                $this->route()->category
                    ? 'unique:categories,name,'.$this->route()->category
                    : 'unique:categories,name'
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
