<?php

namespace App\Http\Controllers;

use App\Models\AdmissionTest;
use App\Models\CustomWebPage;
use App\Models\NationalMensa;
use App\Models\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PageController extends Controller
{
    public function customWebPage(string $pathname)
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
            'has_qualification_of_membership' => $request->user()?->hasQualificationOfMembership,
            'last_attended_admission_test' => $request->user()?->lastAttendedAdmissionTest ? [
                'id' => $request->user()->lastAttendedAdmissionTest->id,
                'testing_at' => $request->user()->lastAttendedAdmissionTest->testing_at,
                'type' => [
                    'interval_month' => $request->user()->lastAttendedAdmissionTest->type->interval_month,
                ],
            ] : null,
            'last_admission_test' => $request->user()?->lastAdmissionTest ? [
                'id' => $request->user()->lastAdmissionTest->id,
                'pivot_is_present' => $request->user()?->lastAdmissionTest?->pivot_is_present,
            ] : null,
            'created_stripe_customer' => $request->user()->stripe ?? null,
            'default_email' => $request->user()?->defaultEmail ? [
                'contact' => $request->user()->defaultEmail->contact,
            ] : null,
            'has_unused_quota_admission_test_order' => $request->user()?->lastAdmissionTestOrder?->hasUnusedQuota ? [
                'quota_expired_on' => $request->user()->lastAdmissionTestOrder->quotaExpiredOn,
            ] : null,
        ];
        $visible = ['id', 'testing_at', 'location', 'address', 'candidates_count', 'maximum_candidates', 'is_free'];
        if ($request->user() && ! $request->user()->hasQualificationOfMembership) {
            $tests = AdmissionTest::where(
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
            array_push($visible, 'minimum_age', 'maximum_age');
        } else {
            $tests = AdmissionTest::with('type:id,minimum_age,maximum_age');
            $visible[] = 'type';
        }
        $tests = $tests->joinRelation('type as type')
            ->withCount('candidates')
            ->with(['address.district.area', 'location'])
            ->where('testing_at', '>=', now())
            ->when(
                $request->user(),
                function ($query) use ($request) {
                    $query->where('is_public', true)
                        ->orWhereHas(
                            'candidates', function ($query) use ($request) {
                                $query->where('user_id', $request->user()->id)
                                    ->where('expect_end_at', '<=', now()->subHour());
                            }
                        );
                },
                function ($query) {
                    $query->where('is_public', true);
                }
            )->orderBy('testing_at')
            ->get();
        $tests->setVisible($visible);
        $tests->each(
            function (AdmissionTest $test) use ($request) {
                $test->address->district->area
                    ->setVisible(['name']);
                $test->address->district
                    ->setVisible(['name', 'area']);
                $test->address->setVisible(['value', 'district']);
                $test->location->setVisible(['name']);
                if (! $request->user() || $request->user()->hasQualificationOfMembership) {
                    $test->type->setVisible(['minimum_age', 'maximum_age']);
                }
            }
        );

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

    public function otherMensaWebsites()
    {
        return Inertia::render(
            'Pages/OtherMensaWebsites',
            [
                'nations' => function () {
                    return NationalMensa::orderBy('name')
                        ->where('is_active', true)
                        ->get(['id', 'name', 'url']);
                },
            ]
        );
    }
}
