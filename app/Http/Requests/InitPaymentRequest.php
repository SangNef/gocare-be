<?php

namespace App\Http\Requests;

class InitPaymentRequest extends Request
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment' => ['required', 'string', 'in:Viettel,Vnpay'],
            'seri_numbers' => ['required', 'string'],
           'ids' => ['required', 'array'],
           'ids.*' => ['required', 'exists:product_series,id'],
           'name' => ['required', 'string', 'max:255'],
           'phone' => ['required', 'regex:/^0/', 'digits_between:10,10'],
           'province' => ['required', 'numeric', 'exists:provinces,id'],
           'district' => ['required', 'numeric', 'exists:districts,id'],
           'ward' => ['required', 'numeric', 'exists:wards,id'],
           'address' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
