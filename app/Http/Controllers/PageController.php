<?php

namespace App\Http\Controllers;

use App\Models\AdmissionTest;
use App\Models\CustomPage;
use App\Models\SiteContent;
use Illuminate\Http\Request;

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
