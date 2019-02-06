<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailFormRequest extends FormRequest
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
            'email' => 'required|email|max:50',//|unique:users,email',
            'platform_email_properties.password' => 'required|max:50|min:6',
            'platform_email_properties.host' => 'required|max:50',
            'platform_email_properties.driver' => 'required|max:50',
            'platform_email_properties.port' => 'required|numeric|max:9999',
            'platform_email_properties.encryption'=> 'required|max:10'
        ];
    }

    public function messages()
    {
        return [
            'required' => "You can't leave the :attribute empty!",
            'email.email' => 'The email has to be an e-mail!',
            'numeric' => "The port must be a number!",
            'max' => "Your :attribute is too long!",
            'min' => "Your :attribute is too short!",
        ];
    }
}
