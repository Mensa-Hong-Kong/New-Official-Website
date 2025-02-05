<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomPageRequest;
use App\Models\CustomPage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CustomPageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Custom Page'))];
    }

    public function index()
    {
        $pages = CustomPage::sortable();

        return view('admin.custom-pages.index')
            ->with('pages', $pages);
    }

    public function create()
    {
        return view('admin.custom-pages.create');
    }

    public function store(CustomPageRequest $request)
    {
        $pathname = preg_replace('/\/+/', '/', $request->pathname);
        if (str_starts_with($pathname, '/')) {
            $pathname = substr($request->pathname, 1);
        }
        CustomPage::create([
            'pathname' => strtolower($pathname),
            'title' => $request->title,
            'og_image_url' => $request->og_image_url,
            'description' => $request->description,
            'content' => $request->content,
        ]);

        return redirect()->route('admin.custom-pages.index');
    }

    public function edit(CustomPage $customPage)
    {
        return view('admin.custom-pages.edit')
            ->with('page', $customPage);
    }

    public function update(CustomPageRequest $request, CustomPage $customPage)
    {
        $pathname = preg_replace('/\/+/', '/', $request->pathname);
        if (str_starts_with($pathname, '/')) {
            $pathname = substr($request->pathname, 1);
        }
        $customPage->update([
            'pathname' => strtolower($pathname),
            'title' => $request->title,
            'og_image_url' => $request->og_image_url,
            'description' => $request->description,
            'content' => $request->content,
        ]);

        return redirect()->route('admin.custom-pages.index');
    }

    public function destroy(CustomPage $customPage)
    {
        $customPage->delete();

        return ['success' => "The custom page of \"{$customPage->title}\" delete success!"];
    }
}
