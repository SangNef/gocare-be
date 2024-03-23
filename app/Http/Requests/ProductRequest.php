<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Dwij\Laraadmin\Models\Module;
use App\Models\Product;

class ProductRequest extends Request
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
        $moduleRules = Module::validateRules("Orders", $this, $this->method() === 'PUT' ? true : false);
        $rules = [
            'height' => 'required|integer|min:0',
            'weight' => 'required|integer|min:0',
            'width' => 'required|integer|min:0',
            'length' => 'required|integer|min:0',
            'sku' => 'required|max:250|regex:/(^[A-Za-z0-9]+$)+/'
        ];
        if ($this->method() === 'POST') {
            $rules['relation_product.*'] = 'required_if:type,' . Product::TYPE_GROUP_PRODUCT . '|integer|min:1';
        }
        return array_merge($moduleRules, $rules);
    }

    public function messages()
    {
        return [
            'sku.regex' => 'Mã sản phẩm không được chứa ký tự đặc biệt'
        ];
    }
}
