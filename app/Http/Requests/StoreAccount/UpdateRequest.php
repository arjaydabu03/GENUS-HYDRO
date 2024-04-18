<?php

namespace App\Http\Requests\StoreAccount;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            "code" => [
                "required",
                "string",

                $this->route()->user_store
                    ? "unique:users_store,account_code," . $this->route()->user_store
                    : "unique:users_store,account_code",
            ],
            "name" => "required|string",
            "location.id" => "required",
            "location.code" => "required",
            "location.name" => "required",
            "department.id" => "required",
            "department.code" => "required",
            "department.name" => "required",
            "company.id" => "required",
            "company.code" => "required",
            "company.name" => "required",
            "scope_order" => ["required"],
            "mobile_no" => [
                "required",
                "regex:[63]",
                "digits:12",
                $this->route()->user_store
                    ? "unique:users_store,mobile_no," . $this->route()->user_store
                    : "unique:users_store,mobile_no",
            ],
        ];
    }

    public function attributes()
    {
        return [
            "scope_order" => "tag store for ordering",
        ];
    }

    public function messages()
    {
        return [
            "required_if" => "The :attribute field is required.",
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
