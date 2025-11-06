<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Login1Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mobile_no' => 'required|regex:/[0-9]{10}/|digits:10',
            'login_otp' => 'required|regex:/[0-9]{6}/|digits:6',
        ];
    }
    public function messages()
    {
       
        return [
            'mobile_no.required' => __('messages.mobilerequired'),
            'mobile_no.regex' => __('messages.invalidMobile'),
            'mobile_no.digits' => __('messages.mobile10digit'),
            'login_otp.required' => __('messages.Otprequired'),
            'login_otp.regex' => __('messages.invalidOtp'),
            'login_otp.digits' => __('messages.otp6digit'),
        ];
    }
}
