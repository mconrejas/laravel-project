<?php

namespace Buzzex\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class WithdrawalRequest extends FormRequest
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
        $rules = [
            'coin' => 'required|valid_exchange_item',
            'address'   => 'required|valid_coin_address',
            'amount'    => 'required|numeric|valid_withdrawal_amount',
            'email_code'=> 'required|valid_code_request'
        ];

        if (auth()->check() && auth()->user()->is2FAEnable()) {
            unset($rules['email_code']);
            $rules['twofa_code']  = 'required|valid_twofa_code';
        }

        return $rules;
    }
}
