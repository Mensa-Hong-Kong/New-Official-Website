<?php

namespace App\Http\Controllers;

use App\Models\AdmissionTest;
use App\Models\CustomWebPage;
use App\Models\SiteContent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    public function customWebPage($pathname)
    {
        $pathname = preg_replace('/\/+/', '/', $pathname);
        if (str_starts_with($pathname, '/')) {
            $pathname = substr($pathname, 1);
        }
        $page = CustomWebPage::select([
                'title',
                'og_image_url',
                'description',
                'content',
            ])->where('pathname', strtolower($pathname))
            ->firstOrFail();

        return Inertia::render('Pages/CustomWebPage')
            ->with('title', $page->title)
            ->with('og_image_url', $page->og_image_url)
            ->with('description', $page->description)
            ->with('content', $page->content);
    }

    public function admissionTests(Request $request)
    {
        return view('admission-tests.index')
            ->with(
                'contents', SiteContent::whereHas(
                    'page', function ($query) {
                        $query->where('name', 'Admission Test');
                    }
                )->get()
                    ->pluck('content', 'name')
                    ->toArray()
            )->with(
                'tests', AdmissionTest::where('testing_at', '>=', now())
                    ->where(
                        function ($query) use ($request) {
                            $query->where('is_public', true);
                            $user = $request->user();
                            if ($user) {
                                $query->orWhereHas(
                                    'candidates', function ($query) use ($request) {
                                        $query->where('user_id', $request->user()->id)->where('expect_end_at', '<=', now()->subHour());
                                    }
                                );
                            }
                        }
                    )->orderBy('testing_at')
                    ->withCount('candidates')
                    ->get()
            );
    }
}
