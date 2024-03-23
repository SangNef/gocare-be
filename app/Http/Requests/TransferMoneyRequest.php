<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class TransferMoneyRequest extends Request
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
            'transfer_bank' => 'required|exists:banks,id',
            'receive_bank' => 'required|different:transfer_bank|exists:banks,id',
        ];
    }
    
    public function messages()
    {
        return [
            'receive_bank.required' => 'Ngân hàng nhận không được để trống',
            'receive_bank.different' => 'Ngân hàng nhận không được giống ngân hàng gửi',
            'transfer_bank.required' => 'Ngân hàng gửi không được để trống',
        ];
    }
}
