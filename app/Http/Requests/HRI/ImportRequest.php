<?php

namespace App\Http\Requests\HRI;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
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
            "*.code" => ["required", "unique:hri,code"],
            "*.name" => ["required"],
        ];
    }
    public function attributes()
    {
        return [
            "*.code" => "code",
        ];
    }

    public function messages()
    {
        return [
            "*.code.unique" => "This :attribute has already been listed",
        ];
    }
}
