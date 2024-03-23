<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Dwij\Laraadmin\Models\Module;

class CODOrderShippingRequest extends Request
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
        $rules = Module::validateRules("CODOrdersShipping", $this, $this->method() === 'PUT' ? true : false);
        $rules = array_merge($rules, [
            'partner_ids' => 'required'
        ]);
        return $rules;
    }

    public function messages()
    {
        return [
            'partner_ids.required' => 'Đơn hàng không được để trống'
        ];
    }
}
