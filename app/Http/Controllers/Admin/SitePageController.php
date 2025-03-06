<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitePage;

class SitePageController extends Controller
{
    public function index()
    {
        return view('admin.site-contents.index')
            ->with('pages', SitePage::all());
    }
}
