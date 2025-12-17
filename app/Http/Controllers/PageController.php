<?php

namespace App\Http\Controllers;

use App\Models\AdmissionTest;
use App\Models\CustomWebPage;
use App\Models\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'description',
            'og_image_url',
            'content',
        ])->where('pathname', strtolower($pathname))
            ->firstOrFail();

        return Inertia::render('Pages/CustomWebPage')
            ->with('page', $page);
    }

    public function admissionTests(Request $request)
    {
        $user = [
            'has_qualification_of_membership' => $request->user()->hasQualificationOfMembership ?? null,
            'last_attended_admission_test' => $request->user()->lastAttendedAdmissionTest ?? false ? [
                'id' => $request->user()->hasQualificationOfMembership->id,
                'testing_at' => $request->user()->hasQualificationOfMembership->testing_at,
                'type' => [
                    'interval_month' => $request->user()->hasQualificationOfMembership->type->interval_month,
                ],
            ] : null,
            'future_admission_test' => $request->user()->futureAdmissionTest ?? false ? [
                'id' => $request->user()->futureAdmissionTest->id,
                'is_free' => $request->user()->futureAdmissionTest->is_free,
            ] : null,
            'created_stripe_customer' => $request->user()->stripe ?? null,
            'default_email' => $request->user()->defaultEmail ?? false ? [
                'contact' => $request->user()->defaultEmail->contact,
            ] : null,
            'has_unused_quota_admission_test_order' => $request->user()->hasUnusedQuotaAdmissionTestOrder()->exists(),
        ];
        $tests = AdmissionTest::joinRelation('type as type')
            ->withCount('candidates')
            ->with(['address.district.area', 'location'])
            ->where('testing_at', '>=', now());
        if ($request->user() && $user['has_qualification_of_membership']) {
            $tests = $tests->where(
                function ($query) use ($request) {
                    $query->whereNull('minimum_age')
                        ->orWhere('minimum_age', '<=', DB::raw("(TIMESTAMPDIFF(MONTH, '{$request->user()->birthday->format('Y-m-d')}', testing_at) - IF(DATE_FORMAT(testing_at, '%d') - {$request->user()->birthday->format('j')} = - 30, 0, 1)) / 12"));
                }
            )->where(
                function ($query) use ($request) {
                    $query->whereNull('maximum_age')
                        ->orWhere('maximum_age', '>=', DB::raw("(TIMESTAMPDIFF(MONTH, '{$request->user()->birthday->format('Y-m-d')}', testing_at) - IF(DATE_FORMAT(testing_at, '%d') - {$request->user()->birthday->format('j')} = - 30, 0, 1)) / 12"));
                }
            );
        } else {
            $tests->with([
                'type' => function ($query) {
                    $query->select(['id', 'minimum_age', 'maximum_age']);
                },
            ]);
        }
        $tests = $tests->where(
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
        foreach ($tests as $test) {
            $test->address->district->area
                ->makeHidden(['id', 'display_order', 'created_at', 'updated_at']);
            $test->address->district
                ->makeHidden(['id', 'area_id', 'display_order', 'created_at', 'updated_at']);
            $test->address->makeHidden(['id', 'district_id', 'created_at', 'updated_at']);
            $test->location->makeHidden(['id', 'created_at', 'updated_at']);
            $test->makeHidden(['type_id', 'address_id', 'location_id', 'expect_end_at', 'is_public', 'created_at', 'updated_at']);
            if (! $request->user()) {
                $test->type->makeHidden('id');
            }
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
