<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CustomPageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Custom Page'))->except('index')];
    }

    public function store(Request $request)
    {
        $pathname = preg_replace('/\/+/', '/', $request->pathname);
        if(str_starts_with($pathname, '/')) {
            $pathname = substr($request->pathname, 1);
        }
        CustomPage::create([
            'pathname' => $pathname,
            'title' => $request->title,
            'og_image_url' => $request->og_image_url,
            'description' => $request->description,
            'content' => $request->content,
        ]);

        return redirect()->route('admin.index');
    }
}
