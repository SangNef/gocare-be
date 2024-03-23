<?php

namespace App\Http\Controllers\ApiV2;

use App\Models\Post;
use App\Models\PostCategory;
use App\Services\Upload;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Models\Page;
use Illuminate\Support\Facades\View;

class PostsController extends Controller
{
    public function getCates()
    {
        return response()->json(['message' => 'Success', 'data' => PostCategory::all()]);
    }

    public function index(Request $request, Upload  $upload)
    {
        $posts = Post::orderBy('created_at', 'desc')
            ->where('status', 'Hiển thị');
        if ($request->cate_id) {
            $posts->whereIn('cate_id', explode(',', $request->cate_id));
        }
        if ($request->s) {
            $posts->where(function ($q) use ($request) {
                $q->where('content', 'LIKE', "%{$request->s}%")
                    ->orWhere('title', 'LIKE', "%{$request->s}%");
            });
        }
        $result = $posts->limit($request->perpage + 1)
            ->offset(($request->get('page', 1) - 1) * $request->perpage)
            ->get();

        $hasMore = $result->count() > $request->perpage;
        $items = $result->splice(0, $request->perpage);

        $items = $items->map(function ($item) use ($upload) {
             $item->image = $item->image ? $upload->getImagePath($item->image) . '?s=600' : '';

             return $item;
        });
        return [
            'items' => $items,
            'hasMore' => $hasMore
        ];
    }

    public function getById(Request $request, $id)
    {
        $post = Post::find($id);

        return response()->json(['message' => 'Success', 'data' => $post]);
    }
}
