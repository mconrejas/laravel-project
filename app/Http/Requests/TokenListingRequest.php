<?php

namespace Buzzex\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TokenListingRequest extends FormRequest
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
        $rules = [
            'symbol' => 'required|string|min:2|unique:coin_projects,symbol',
            'name'   => 'required|string|min:2|unique:coin_projects,name',
            'coin_type' => 'required|string|in:'.implode(',', ['Public Chain', 'Non Public Chain']),
            'date_of_issue' => 'sometimes|nullable|date',
            'total_supply'  => 'required|numeric|min:1000',
            'official_website' => 'required|url',
            'project_description' => 'required|string|min:100',
            'whitepaper' => 'sometimes|nullable|url',
            'source_code' => 'required|url',
            'blockchain_explorer' => 'required|string|min:10|valid_token_explorer_url',
            'logo'   =>  'required|image|mimes:png|max:'.maximumFileUploadSize()
        ];
        if ((int) parameter('recaptcha_enable', 1) == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        return $rules;
    }

    /**
     * Custom message for validating coin projects
     * @return array
     */
    public function messages()
    {
        return array(
            'project_description.min' => __('Project description should be atleast 100 characters'),
            'total_supply.min' => __('Total supply should be atleast 1000'),
            'blockchain_explorer.min' => __('Blockchain explorer should be atleast 10 characters'),
            'blockchain_explorer.valid_token_explorer_url' => __('Blockchain explorer url format is invalid'),
        );
    }
}
