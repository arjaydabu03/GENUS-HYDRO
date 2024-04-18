<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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

                $this->route()->id
                    ? "unique:users,account_code," . $this->route()->id
                    : "unique:users,account_code",
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
            "scope_approval" => ["required_if:role_id,2", "array"],
            "scope_order" => ["required_if:role_id,3", "array"],
            "role_id" => "required|exists:role,id,deleted_at,NULL",
            "mobile_no" => [
                "required_if:role_id,7",
                "exclude_unless:role_id,7",
                "regex:[63]",
                "digits:12",

                $this->route()->id
                    ? "unique:users,mobile_no," . $this->route()->id
                    : "unique:users,mobile_no",
            ],
            "username" => [
                "required",
                "string",
                $this->route()->id
                    ? "unique:users,username," . $this->route()->id
                    : "unique:users,username",
            ],
        ];
    }

    public function attributes()
    {
        return [
            "scope_approval" => "scope for approval",
            "scope_order" => "scope for ordering",
            "mobile_no" => "mobile no",
        ];
    }

    public function messages()
    {
        return [
            "required_if" => "The :attribute field is required.",
            "exists" => "Role is not Registered",
            "regex" => "The :attribute format must start with 63.",
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
