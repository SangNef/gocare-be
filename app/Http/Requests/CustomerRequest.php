<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CustomerRequest extends Request
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
            'name' => 'sometimes|required|min:6',
            'phone' => 'sometimes|required|regex:/^0/|digits_between:10,10',
            'address' => 'sometimes|required',
            'province' => 'sometimes|required|exists:provinces,id',
            'district' => 'sometimes|required|exists:districts,id,province_id,' . $this->province,
            'ward' => 'sometimes|required|exists:wards,id,district_id,' . $this->district,
            'password' => 'sometimes|confirmed|min:6',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên không được để trống',
            'name.min' => 'Tên phải tối thiểu 6 ký tự',
            'phone.required' => 'SĐT không được để trống',
            "phone.regex" => "Số điện thoại không hợp lệ",
            'address.required' => 'Địa chỉ không được để trống',
            'province.required' => 'Tỉnh/Thành phố không được để trống',
            'province.exists' => 'Tỉnh/Thành phố không tồn tại',
            'district.required' => 'Quận/Huyện không được để trống',
            'district.exists' => 'Quận/Huyện không tồn tại',
            'ward.required' => 'Phường/Xã không được để trống',
            'ward.exists' => 'Phường/Xã không tồn tại',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp',
            'password.min' => 'Mật khẩu tối thiểu 6 ký tự'
        ];
    }
}
