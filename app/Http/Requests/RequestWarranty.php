<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class RequestWarranty extends Request
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
            'name' => 'required',
            'phone' => 'required|regex:/^0/|digits_between:10,10',
            'address' => 'required',
            'province' => 'required|exists:provinces,id',
            'district' => 'required|exists:districts,id,province_id,' . $this->province,
            'ward' => 'required|exists:wards,id,district_id,' . $this->district,
            'seri' => 'required|exists:product_series,seri_number|unique:request_warranties,seri_number',
            'content' => 'required|max:1000',
            'images.*' => 'mimes:png,jpg,jpeg|max:500'
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
            'seri.required' => 'Số seri phố không được để trống',
            'seri.exists' => 'Số seri không tồn tại',
            'seri.unique' => 'Đã tạo yêu cầu bảo hành với mã seri này',
            'content.required' => 'Nội dung phố không được để trống',
            'content.max' => 'Nội dung không tồn tại',
            'images.*.max' => 'Dung lượng file phải nhỏ hơn 500kb',
            'images.*.mimes' => 'Địch dạng file phải là jpg,jpeg,png'
        ];
    }
}
