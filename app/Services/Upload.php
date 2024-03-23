<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupProductDiscount;
use App\Models\Product;
use App\Models\Upload as UploadModel;
use Illuminate\Support\Facades\Auth;

class Upload
{
    public function getThumbnail($uploadId, $width = 60)
    {
        $upload = $uploadId
            ? UploadModel::find($uploadId)
            : null;
        return '<img width="' . $width . '" src="'
            . ($upload
                ? $upload->path()
                : url('la-assets/img/default.png'))
            . '?s=130" />';
    }

    public function getImagePath($uploadId)
    {
        $upload = UploadModel::find($uploadId);
        return $upload ? UploadModel::find($uploadId)->path() : '';
    }

    public function storeFile($fileName, $path)
    {
        $upload = UploadModel::create([
            "name" => $fileName,
            "path" => $path,
            "extension" => pathinfo($fileName, PATHINFO_EXTENSION),
            "caption" => "",
            "hash" => "",
            "public" => config("laraadmin.uploads.default_public"),
            "user_id" => Auth::check() ? Auth::user()->id : 1
        ]);
        while (true) {
            $hash = strtolower(str_random(20));
            if (!UploadModel::where("hash", $hash)->count()) {
                $upload->hash = $hash;
                break;
            }
        }
        $upload->save();
        return $upload;
    }
}
