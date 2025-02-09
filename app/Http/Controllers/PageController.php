<?php

namespace App\Http\Controllers;

use App\Models\CustomPage;

class PageController extends Controller
{
    public function customPage($pathname)
    {
        $pathname = preg_replace('/\/+/', '/', $pathname);
        if (str_starts_with($pathname, '/')) {
            $pathname = substr($pathname, 1);
        }
        $page = CustomPage::where('pathname', strtolower($pathname))
            ->firstOrFail();

        return view('pages.custom-page')
            ->with('page', $page);
    }
}
