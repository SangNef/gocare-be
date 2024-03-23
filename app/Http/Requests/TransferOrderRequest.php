<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Dwij\Laraadmin\Models\Module;

class TransferOrderRequest extends Request
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
            'code' => 'required',
            'customer_id' => 'required|exists:customers,id',
            'seris' => 'required',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'seris' => 'Seri sản phẩm không được để trống',
            'code' => 'Mã đơn hàng không được để trống',
            'customer_id' => 'Người nhận không được để trống',
        ];
    }
}
