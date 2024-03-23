<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ProductCombo extends Model
{
    use SoftDeletes;
	
	protected $table = 'productcombos';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function groups()
    {
        return collect(DB::table('product_combo_groups')
            ->where('combo_id', $this->id)
            ->get())->pluck('discount', 'group_id');
    }

    public function groupsWithName()
    {
        return DB::table('product_combo_groups')
            ->select(DB::raw('display_name, discount, group_id'))
            ->join('groups', 'groups.id', '=', 'product_combo_groups.group_id')
            ->where('combo_id', $this->id)
            ->where('discount', '>', 0)
            ->get();
    }

    public function isApplyForGroup($groupId)
    {
        return DB::table('product_combo_groups')
            ->where('combo_id', $this->id)
            ->where('group_id', $groupId)
            ->where('discount', '>', 0)
            ->count() > 0;
    }
}
