<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordFormRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'required|confirmed|max:255|min:6'
        ];
    }

    public function messages()
    {
        return [
            'required' => "Type a new password!",
            'confirmed' => "Entered passwords don't match!",
            'max' => "Your :attribute is too long!",
            'min' => "Your :attribute is too short! (min 6)"
        ];
    }
}
