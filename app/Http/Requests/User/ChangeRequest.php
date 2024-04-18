<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class ChangeRequest extends FormRequest
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
            'old_password'=>'required',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $id=Auth::id();
            $user = User::find($id);
  
            if (! Hash::check($this->get('old_password'), $user->password))
            {
             $validator->errors()->add("old_password", "Invalid credentials.");
            }
            
          
            // $validator->errors()->add("custom", $this->route()->id);
        });
    }
}
