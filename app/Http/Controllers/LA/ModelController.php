<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Doctrine\DBAL\Schema\Schema;
use Illuminate\Http\Request;
use Auth;
use DB;
use Validator;
use Datatables;
use App\User;
use App\Models\UsersBalanceTracking;
use App\Models\UsersEwallet;
use App\Models\Withdraw;
use App\Models\Deposit;

class ModelController extends Controller
{
    public function index(Request $request)
    {
        switch ($request->model) {
            case 'user':
                $table = 'users';
                $cols = ['name', 'id'];
                $where = [];
                break;
            case 'productcategory':
                $table = 'productcategories';
                $cols = ['name', 'id'];
                $where = [];
                break;
            case 'product':
                $table = 'products';
                $cols = [['name', 'sku'], 'id'];
                $where = [['deleted_at', '=', null]];
                if (isset($request->extra_param)) {
                    $where[] = ['status', '=', 1];
                }
                break;
            case 'seri':
                $table = 'product_series';
                $cols = ['seri_number', 'seri_number'];
                $where = [];
                if (isset($request->extra_param)) {
                    $where[] = ['product_id', '=', $request->extra_param];
                }
                break;
            case 'product_for_switch':
                $table = 'products';
                $cols = [['sku', 'name'], 'id'];
                $where = [['status', '=', 1]];
                break;
            case 'banks':
                $table = 'banks';
                $cols = [['name', 'branch', 'acc_name', 'acc_id'], 'id'];
                $where = [['deleted_at', '=', NULL]];
                if (isset($request->extra_param)) {
                    $where[] = ['currency_type', '=', $request->extra_param];
                }
                if (isset($request->store_id)) {
                    $where[] = ['store_id', '=', $request->store_id];
                }
                break;
            case 'customer':
                $table = 'customers';
                $cols = [['username', 'name', 'phone'], 'id'];
                $where = [['deleted_at', '=', NULL]];
                if (isset($request->extra_param)) {
                    $where[] = ['customer_currency', '=', $request->extra_param];
                }
                if (isset($request->store_id)) {
                    $where[] = ['store_id', '=', $request->store_id];
                }

                break;
            case 'customer-email':
                $table = 'customers';
                $cols = ['email', 'email'];
                $where = [['deleted_at', '=', NULL]];
                break;
            case 'group':
                $table = 'groups';
                $cols = ['display_name', 'id'];
                $where = [['deleted_at', '=', NULL]];
                if (isset($request->store_id)) {
                    $where[] = ['store_id', '=', $request->store_id];
                }
                break;
            case 'draft_order':
                $table = 'draft_orders';
                $cols = ['order_code', 'id'];
                $where = [];
                break;
            case 'customer_orders':
                $table = 'orders';
                $cols = ['code', 'id'];
                $where = [['customer_id', '=', $request->extra_param], ['status', '=', 1], ['deleted_at', '=', NULL]];
                break;
            case 'store_owner':
                $table = 'customers';
                $cols = ['name', 'id'];
                $where = [['store_id', '=', NULL], ['deleted_at', '=', NULL]];
                break;
            case 'stores':
                $table = 'stores';
                $cols = ['name', 'id'];
                $where = [['deleted_at', '=', NULL]];
                break;
            case 'ward_for_cod':
                $table = 'wards';
                $cols = ['name', 'name'];
                $where = [];
                break;
            case 'attribute':
                $table = 'attributes';
                $cols = ['name', 'id'];
                $where = [];
                break;
            case 'attribute_value':
                $table = 'attributevalues';
                $cols = ['value', 'id'];
                $where = [['attribute_id', '=', $request->extra_param]];
                break;
            case 'group_attribute':
                $table = 'product_attributes_value_media';
                $where = [['product_id', '=', $request->extra_param]];
                $cols = ['attribute_value_texts', 'id'];
                break;
            default:
                $table = '';
                $where = [];
        }
        if (auth()->check() && auth()->user()->store_id && \Schema::hasColumn($table, 'store_id')) {
            $where[] = [
                'store_id',
                '=',
                auth()->user()->store_id
            ];
        }

        return $table
            ? Db::table($table)
            ->select(DB::raw($this->prepareTextColum($cols[0]) . ' as text'), DB::raw($cols[1] . ' as id'))
            ->where(function ($query) use ($cols) {
                if (request('q')) {
                    if (is_array($cols[0])) {
                        foreach ($cols[0] as $index => $col) {
                            if ($index > 0) {
                                $query->orWhere($col, 'like', '%' . request('q') . '%');
                            } else {
                                $query->where($col, 'like', '%' . request('q') . '%');
                            }
                        }
                    } else {
                        $query->where($cols[0], 'like', '%' . request('q') . '%');
                    }
                }
            })
            ->where(function ($query) use ($where) {
                foreach ($where as $w) {
                    if (count($w) === 3 && $w = array_values($w)) {
                        $query->where($w[0], $w[1], $w[2]);
                    }
                }
            })
            ->paginate()
            : [];
    }

    protected function prepareTextColum($cols)
    {
        $result = $cols;

        if (is_array($cols)) {
            $result = 'CONCAT (' . implode(', "-", ', $cols) . ')';
        }

        return $result;
    }
}
