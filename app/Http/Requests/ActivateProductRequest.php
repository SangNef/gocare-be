<?php

namespace App\Http\Requests;

class ActivateProductRequest extends Request
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

    public function rules()
    {
        return [
            'name' => 'required',
            'phone' => 'required|regex:/^0/|digits_between:10,10',
            'province' => 'required|exists:provinces,id',
            'district' => 'required|exists:districts,id',
            'ward' => 'required|exists:wards,id',
            'address' => 'required',
            'activation_code' => 'required_with:phone_info',
            'customer_code' => 'sometimes|exists:customers,code'
        ];
    }

    public function messages()
    {
        return [
            "phone.digits_between" => "Số điện thoại không hợp lệ",
            "phone.regex" => "Số điện thoại không hợp lệ",
            "activation_code.required_with" => 'Mã kích hoạt không hợp lệ',
            'customer_code.exists' => 'Mã đại lý không hợp lệ'
        ];
    }
}