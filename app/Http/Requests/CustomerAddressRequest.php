<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CustomerAddressRequest extends Request
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
            'province' => 'required',
            'district' => 'required',
            'ward' => 'required',
        ];
    }
}
