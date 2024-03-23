<?php

namespace App\Http\Controllers\ApiV2;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Models\Page;
use Illuminate\Support\Facades\View;

class PagesController extends Controller
{
    public function getPageBySlug($slug)
    {
        $page = Page::where('slug', $slug)->first();
        return $page
            ? response()->json([
                'message' => 'Success',
                'data' => [
                    'p_id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'content' => html_entity_decode($page->content)
                ]
            ])
            : response()->json(['message' => 'Không tìm thấy trang'], 404);
    }

    public function create()
    {
        $html = View::make('apis.pages.create')->render();
        return response()->json([
            'message' => 'Success',
            'html' => $html
        ]);
    }

    public function store(PageRequest $request)
    {
        Page::create([
            'title' => $request->title,
            'slug' => str_slug($request->title),
            'content' => html_entity_decode($request->content)
        ]);
        return response()->json(['message' => 'Tạo trang thành công']);
    }

    public function edit($slug)
    {
        $page = Page::where('slug', $slug)->first();
        if ($page) {
            $html = View::make('apis.pages.edit', compact($page))->render();
            return response()->json([
                'message' => 'Success',
                'html' => $html
            ]);
        }
        return response()->json(['message' => 'Không tìm thấy trang'], 404);
    }

    public function update($slug, PageRequest $request)
    {
        $page = Page::where('slug', $slug)->first();
        if ($page) {
            $page->update([
                'title' => $request->title,
                'slug' => str_slug($request->title),
                'content' => html_entity_decode($request->content)
            ]);
            return response()->json(['message' => 'Cập nhật thành công']);
        }
        return response()->json(['message' => 'Không tìm thấy trang'], 404);
    }
}
