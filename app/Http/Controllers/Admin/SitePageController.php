<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteContentRequest;
use App\Models\SiteContent;
use App\Models\SitePage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SitePageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Site Content'))];
    }

    public function index()
    {
        return view('admin.site-contents.index')
            ->with('pages', SitePage::all());
    }

    public function update(SiteContentRequest $request, SiteContent $siteContent)
    {
        $siteContent->update(['content' => $request->content]);

        return redirect()->route('admin.site-contents.index');
    }
}
