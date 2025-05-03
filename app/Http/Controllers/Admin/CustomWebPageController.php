<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomWebPageRequest;
use App\Models\CustomWebPage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CustomWebPageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Custom Web Page'))];
    }

    public function index()
    {
        $pages = CustomWebPage::sortable()->get();

        return view('admin.custom-web-pages.index')
            ->with('pages', $pages);
    }

    public function create()
    {
        return view('admin.custom-web-pages.create');
    }

    public function store(CustomWebPageRequest $request)
    {
        $pathname = preg_replace('/\/+/', '/', $request->pathname);
        if (str_starts_with($pathname, '/')) {
            $pathname = substr($request->pathname, 1);
        }
        CustomWebPage::create([
            'pathname' => strtolower($pathname),
            'title' => $request->title,
            'og_image_url' => $request->og_image_url,
            'description' => $request->description,
            'content' => $request->content,
        ]);

        return redirect()->route('admin.custom-web-pages.index');
    }

    public function edit(CustomWebPage $customWebPage)
    {
        return view('admin.custom-web-pages.edit')
            ->with('page', $customWebPage);
    }

    public function update(CustomWebPageRequest $request, CustomWebPage $customWebPage)
    {
        $pathname = preg_replace('/\/+/', '/', $request->pathname);
        if (str_starts_with($pathname, '/')) {
            $pathname = substr($request->pathname, 1);
        }
        $customWebPage->update([
            'pathname' => strtolower($pathname),
            'title' => $request->title,
            'og_image_url' => $request->og_image_url,
            'description' => $request->description,
            'content' => $request->content,
        ]);

        return redirect()->route('admin.custom-web-pages.index');
    }

    public function destroy(CustomWebPage $customWebPage)
    {
        $customWebPage->delete();

        return ['success' => "The custom web page of \"{$customWebPage->title}\" delete success!"];
    }
}
