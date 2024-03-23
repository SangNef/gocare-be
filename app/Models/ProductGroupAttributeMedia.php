<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductGroupAttributeMedia extends Model
{
	protected $table = 'product_attributes_value_media';

	protected $hidden = [];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function products()
	{
		return $this->belongsTo(Product::class);
	}

	public function getMedia($thumbnail = true)
    {
        $sv = app(\App\Services\Upload::class);
        $media = explode(',', $this->media_ids);
        return Upload::whereIn('id', $media)
            ->get()
            ->map(function ($upload) use ($sv, $thumbnail) {
                $path = $thumbnail ? $sv->getThumbnail($upload->id) : $sv->getImagePath($upload->id);
                return $path;
            });
    }
}
