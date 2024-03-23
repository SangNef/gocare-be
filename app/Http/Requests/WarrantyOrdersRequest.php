<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Dwij\Laraadmin\Models\Module;

class WarrantyOrdersRequest extends Request
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
            'products' => 'required',
            'products.*.quantity' => 'required|integer|min:0',
            'series' => 'required|array',
            'series.*.seri_id' => 'exists:product_series,id'
        ];

        $rules = array_merge($moduleRules, $rules);
        return $rules;
    }

    public function messages()
    {
        return [
            'series.*.seri_id.exists' => 'Seri sản phẩm không tồn tại'
        ];
    }
}
