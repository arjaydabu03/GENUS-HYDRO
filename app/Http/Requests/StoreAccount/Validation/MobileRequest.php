<?php

namespace App\Http\Requests\StoreAccount\Validation;

use Illuminate\Foundation\Http\FormRequest;

class MobileRequest extends FormRequest
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
            "mobile_no" => [
                "required",
                $this->get("id")
                    ? "unique:users_store,mobile_no," . $this->get("id")
                    : "unique:users_store,mobile_no",
            ],
        ];
    }
}
