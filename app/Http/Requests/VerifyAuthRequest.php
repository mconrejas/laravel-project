<?php

namespace Buzzex\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VerifyAuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'current-password' => 'required|verify_logged_in_user',
            'twofa_code'    => 'required|valid_twofa_code'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'current-password.verify_logged_in_user' => __('Incorrect password. Please try again.'),
            'twofa_code.valid_twofa_code' => __('Invalid 2FA code.'),
        ];
    }
}
