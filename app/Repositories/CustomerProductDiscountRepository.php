<?php

namespace App\Repositories;

use App\Models\CustomerProductDiscount;


class CustomerProductDiscountRepository
{
	public function save($products = [], $customerId)
	{
		foreach ($products as $product) {
			if (isset($product['save']) && $product['save'] == 1) {
				CustomerProductDiscount::updateOrCreate([
					'customer_id' => $customerId,
					'product_id' => $product['product_id'],
				], [
					'discount' => $product['price'],
					'creator_id' => auth()->user()->id
				]);
			}
		}
	}
}

