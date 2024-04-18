<?php

namespace App\Http\Requests\Charge;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function rules()
    {
        return [
            "*.code" => "required|string",
            "*.name" => "required|string",
            "*.sync_id" => "required|integer",
        ];
    }

    public function attributes()
    {
        return [
            "*.code" => "code",
            "*.name" => "name",
            "*.sync_id" => "sync_id",
        ];
    }
}
