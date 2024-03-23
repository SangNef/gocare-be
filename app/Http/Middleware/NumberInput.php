<?php

namespace App\Http\Middleware;

use Closure;

class NumberInput
{
    protected $inputs = [
        'price',
        'price_in_ndt',
        'n_quantity',
        'w_quantity',
        'r_quantity',
        'fee',
        'amount',
        'transfer_amount',
        'received_amount',
        'transfer_fee',
        'receive_fee',
        'quantity',
        'first_balance',
        'last_balance',
        'debt_in_advance',
        'debt_total',
        'discount',
        'amount_vnd',
        'amount_ndt',
        'MONEY_COLLECTION',
        'PRODUCT_PRICE',
        'insurance_value',
        'fee_amount',
        'cod_amount',
        'price_per_package',
        'total',
        'transport_price',
        'package_price',
        'real_amount',
        'CodAmountEvaluation',
        'OrderAmountEvaluation',
        'cod_price_statement',
        'max',
        'min_order_amount'
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $input = $request->all();
        array_walk_recursive($input, function (&$input, $key) use ($request) {
            $pattern = $request->is('api/*') ? '/[^\d-]+/' : '/[^\d.-]+/';
            if (in_array($key, $this->inputs)) {
                $input = preg_replace($pattern, '', $input);
            }
        });
        $request->merge($input);
        return $next($request);
    }
}
