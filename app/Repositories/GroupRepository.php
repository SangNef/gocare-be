<?php

namespace App\Repositories;

use App\Models\Bank;
use App\Models\Group;
use App\Models\GroupCateDiscount;

class GroupRepository
{
    public function getDiscounts($groupId = '')
    {
        $groups = Group::whereNull('deleted_at');
        if ($groupId) {
            $groups->where('id', $groupId);
        }
        $groups = $groups->get()->map(function ($group) {
            $discount = [];
            $cates = GroupCateDiscount::where('group_id', $group->id)
                ->where('type', 1)
                ->get();
            foreach ($cates as $cate) {
                $discount[$cate->cate_id][$cate->quantity] = [
                    'quantity' => $cate->quantity,
                    'discount' => $cate->discount,
                    'discount_1' => (int) $cate->discount_1,
                ];
                krsort($discount[$cate->cate_id]);
            }
            $group->discount = $discount;

           return [
               'id' => $group->id,
               'discount' => $discount,
           ];
        })->keyBy('id');

        return $groups->toArray();
    }
}
