<?php

namespace Buzzex\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuySellRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'form_type' => 'required|string|in:' . implode(',', ['limit', 'market', 'stop-limit']),
            'action' => 'required|string|in:' . implode(',', ['buy', 'sell']),
            'pair_id' => 'required|integer|valid_pair',
            'price' => 'required|numeric|min:0.00000001',
            'amount' => 'required|numeric|min:0.00000001',
        ];

        if ($this->get('form_type') === 'market') {
            unset($rules['price']);
        }

        if ($this->get('form_type') === 'stop-limit') {
            unset($rules['price']);

            $rules = array_merge($rules, [
                'stop' => 'required|numeric',
                'limit' => 'required|numeric',
            ]);
        }

        return $rules;
    }
}
