<?php

namespace App\Http\Requests\Role;

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
            "name" => [
                "required",
                "string",
                $this->route()->uom
                    ? "unique:role,name," . $this->route()->uom
                    : "unique:role,name",
            ],
            "access_permission" => ["array", "min:1"],
        ];
    }
}
