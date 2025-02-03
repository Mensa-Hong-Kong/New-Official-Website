<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use Illuminate\Http\Request;

class CustomPageController extends Controller
{
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
