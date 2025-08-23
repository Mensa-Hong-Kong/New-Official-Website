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
        $user = [
            'has_qualification_of_membership' => $request->user()->hasQualificationOfMembership ?? null,
            'last_attended_admission_test' => $request->user()->lastAttendedAdmissionTest ? [
                'id' => $request->user()->hasQualificationOfMembership->id,
                'testing_at' => $request->user()->hasQualificationOfMembership->testing_at,
                'type' => [
                    'interval_month' => $request->user()->hasQualificationOfMembership->type->interval_month,
                ],
            ] : null,
            'future_admission_test' => $request->user()->futureAdmissionTest ? [
                'id' => $request->user()->futureAdmissionTest->id,
            ] : null,
            'created_stripe_account' => (bool) $request->user()->stripe,
            'default_email' => $request->user()->defaultEmail ? [
                'contact' => $request->user()->defaultEmail->contact,
            ] : null,
        ];
        $tests = AdmissionTest::withCount('candidates')
            ->with(['address.district.area', 'location'])
            ->where('testing_at', '>=', now())
            ->where(
                function ($query) use ($request) {
                    $query->where('is_public', true);
                    if ($request->user()) {
                        $query->orWhereHas(
                            'candidates', function ($query) use ($request) {
                                $query->where('user_id', $request->user()->id)
                                    ->where('expect_end_at', '<=', now()->subHour());
                            }
                        );
                    }
                }
            )->orderBy('testing_at')
            ->get();
        foreach($tests as $test) {
            $test->address->district->area
                ->makeHidden(['id', 'display_order', 'created_at', 'updated_at']);
            $test->address->district
                ->makeHidden(['id', 'area_id', 'display_order', 'created_at', 'updated_at']);
            $test->address->makeHidden(['id', 'district_id', 'created_at', 'updated_at']);
            $test->location->makeHidden(['id', 'created_at', 'updated_at']);
            $test->makeHidden(['type_id', 'address_id', 'location_id', 'expect_end_at', 'is_public', 'created_at', 'updated_at']);
        }

        return Inertia::render('AdmissionTests/Index')
            ->with('user', $user)
            ->with(
                'contents', SiteContent::whereHas(
                    'page', function ($query) {
                        $query->where('name', 'Admission Test');
                    }
                )->get()
                    ->pluck('content', 'name')
                    ->toArray()
            )->with('tests', $tests);
    }
}
