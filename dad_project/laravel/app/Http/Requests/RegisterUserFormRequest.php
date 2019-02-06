<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
//        $this->user();
//          anyone can send this form
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
            'name' => 'required|max:255',
            'nickname' => 'required|max:255|unique:users,nickname',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|confirmed|max:255|min:6'
        ];
    }

    public function messages()
    {
        return [
            'required' => "You can't leave the :attribute empty!",
            'email.email' => 'The email has to be an e-mail!',
            'unique' => "Somebody's already using that :attribute!",
            'confirmed' => "Entered passwords don't match!",
            'max' => "Your :attribute is too long!",
            'min' => "Your :attribute is too short! (min 6)"
        ];
    }
}
