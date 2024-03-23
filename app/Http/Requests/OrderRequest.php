<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Dwij\Laraadmin\Models\Module;

class OrderRequest extends Request
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
        $method = $this->method();
        $moduleRules = Module::validateRules("Orders", $this, $method === 'PUT' ? true : false);
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'created_at' => 'required|date_format:Y/m/d|before:tomorrow',
            'payment.*.amount' => 'required|min:0',
            'payment.*.paid_date' => 'required|date_format:Y/m/d|before:tomorrow',
            'use_transport' => 'sometimes',
            'transport.customer_id' => 'required_if:use_transport,on',
            'transport.unit' => 'required_if:use_transport,on'
        ];
        if ($method === 'POST') {
            $rules += [
                'code' => 'required|unique:orders,code',
                'products' => 'required',
                'products.*.n_quantity' => 'required|integer|min:0',
                'products.*.w_quantity' => 'required|integer|min:0',
                'products.*.price' => 'required|min:0',
                'fee' => 'required|min:0',
                'discount' => 'required|integer|min:0',
                'discount_percent' => 'required|between:0,99.99',
            ];
        }
        if ($this->order_series_type == 1) {
            $rules['products.*.series'] = 'required_if:products.*.has_series,1|series_equal';
        }
        foreach ($this->payment as $paid) {
            if ($paid['amount'] > 0 && empty($paid['bank_id'])) {
                $rules['payment.*.bank_id'] = 'required';
            }
        }
        $rules = array_merge($moduleRules, $rules);
        return $rules;
    }

    public function messages()
    {
        return [
            'products.*.series.required_if' => 'Seri sản phẩm không được để trống',
            'products.*.series.series_equal' => 'Số lượng Seri phải bằng số lượng sản phẩm',
            'transport.customer_id.required_if' => 'Đơn vị vận chuyển không được để trống',
            'transport.unit.required_if' => 'Đơn vị không được để trống'
        ];
    }

    protected function validationData()
    {
        $products = $this->get('products');
        foreach ($products as $key => $product) {
            if (isset($product['series'])) {
                $product['series'] = array_filter(explode(',', $product['series']), function ($v) {
                    return trim($v);
                });
            }
            $products[$key] = $product;
        }
        $this->merge([
            'products' => $products
        ]);

        return $this->all();
    }
}
