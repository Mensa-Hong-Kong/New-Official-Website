<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteContentRequest;
use App\Models\SiteContent;
use App\Models\SitePage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class SiteContentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Site Content'))];
    }

    public function index()
    {
        $pages = SitePage::with([
            'contents' => function ($query) {
                $query->select(['id', 'name', 'page_id']);
            },
        ])->select(['id', 'name'])
            ->get();
        foreach ($pages as $page) {
            $page->contents->makeHidden('page_id');
        }

        return Inertia::render('Admin/SiteContents/Index')
            ->with('pages', $pages);
    }

    public function edit(SiteContent $siteContent)
    {
        $siteContent->load([
            'page' => function ($query) {
                $query->select(['id', 'name']);
            },
        ]);
        $siteContent->makeHidden(['page_id', 'created_at', 'updated_at']);
        $siteContent->page->makeHidden('id');

        return Inertia::render('Admin/SiteContents/Edit')
            ->with('content', $siteContent);
    }

    public function update(SiteContentRequest $request, SiteContent $siteContent)
    {
        $siteContent->update(['content' => $request->content]);

        return redirect()->route('admin.site-contents.index');
    }
}
