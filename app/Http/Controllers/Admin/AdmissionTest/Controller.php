<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\Admin\AdmissionTest\TestRequest;
use App\Models\Address;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestType;
use App\Models\Area;
use App\Models\Location;
use App\Notifications\AdmissionTest\Admin\CanceledAdmissionTest;
use App\Notifications\AdmissionTest\Admin\RemovedAdmissionTestRecordByQueue;
use App\Notifications\AdmissionTest\Admin\UpdateAdmissionTest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\EncryptHistoryMiddleware;
use Inertia\Inertia;

class Controller extends BaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            (new Middleware(EncryptHistoryMiddleware::class))->only('show'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    if (
                        $request->user()->proctorTests()->count() ||
                        $request->user()->canAny([
                            'Edit:Admission Test',
                            'Edit:Admission Test Proctor',
                            'View:Admission Test Candidate',
                            'Edit:Admission Test Candidate',
                            'View:Admission Test Result',
                            'Edit:Admission Test Result',
                        ])
                    ) {
                        return $next($request);
                    }
                    abort(403);
                }
            ))->only('index'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $test = $request->route('admission_test');
                    $test->append(['current_user_is_proctor', 'in_testing_time_range']);
                    if (
                        $request->user()->canAny([
                            'Edit:Admission Test',
                            'Edit:Admission Test Proctor',
                            'View:Admission Test Candidate',
                            'Edit:Admission Test Candidate',
                            'View:Admission Test Result',
                            'Edit:Admission Test Result',
                        ]) || ($test->in_testing_time_range && $test->current_user_is_proctor)
                    ) {
                        return $next($request);
                    }
                    abort(403);
                }
            ))->only('show'),
            (new Middleware('permission:Edit:Admission Test'))->except(['index', 'show']),
        ];
    }

    public function index(Request $request)
    {
        if ($request->user()->can('Edit:Admission Test')) {
            $tests = new AdmissionTest;
        } else {
            $tests = $request->user()->proctorTests();
        }
        $tests = $tests->withCount('candidates')
            ->with([
                'location' => function ($query) {
                    $query->select(['id', 'name']);
                },
            ])->sortable('testing_at')->paginate();
        $tests->append(['in_testing_time_range', 'current_user_is_proctor']);
        $tests->makeHidden(['type_id', 'expect_end_at', 'location_id', 'address_id', 'created_at', 'updated_at']);
        foreach ($tests as $test) {
            $test->location->makeHidden('id');
        }

        return Inertia::render('Admin/AdmissionTests/Index')
            ->with('tests', $tests);
    }

    public function create()
    {
        $areas = Area::with([
            'districts' => function ($query) {
                $query->orderBy('display_order');
            },
        ])->orderBy('display_order')
            ->get();
        $districts = [];
        foreach ($areas as $area) {
            $districts[$area->name] = [];
            foreach ($area->districts as $district) {
                $districts[$area->name][$district->id] = $district->name;
            }
        }

        return Inertia::render('Admin/AdmissionTests/Create')
            ->with(
                'types', AdmissionTestType::orderBy('display_order')
                    ->get(['id', 'name'])
                    ->pluck('name', 'id')
                    ->toArray()
            )->with(
                'locations', Location::distinct()
                    ->get('name')
                    ->pluck('name')
                    ->toArray()
            )->with('districts', $districts)
            ->with(
                'addresses', Address::distinct()
                    ->get('value')
                    ->pluck('value')
                    ->toArray()
            );
    }

    public function store(TestRequest $request)
    {
        DB::beginTransaction();
        $location = Location::firstOrCreate([
            'name' => $request->location,
        ]);
        $address = Address::firstOrCreate([
            'district_id' => $request->district_id,
            'value' => $request->address,
        ]);
        $test = AdmissionTest::create([
            'type_id' => $request->type_id,
            'testing_at' => $request->testing_at,
            'expect_end_at' => $request->expect_end_at,
            'location_id' => $location->id,
            'address_id' => $address->id,
            'maximum_candidates' => $request->maximum_candidates,
            'is_free' => (bool) $request->is_free,
            'is_public' => (bool) $request->is_public,
        ]);
        DB::commit();

        return redirect()->route(
            'admin.admission-tests.show',
            ['admission_test' => $test]
        );
    }

    public function show(Request $request, AdmissionTest $admissionTest)
    {
        $areas = Area::with([
            'districts' => function ($query) {
                $query->orderBy('display_order');
            },
        ])->orderBy('display_order')
            ->get();
        $districts = [];
        foreach ($areas as $area) {
            $districts[$area->name] = [];
            foreach ($area->districts as $district) {
                $districts[$area->name][$district->id] = $district->name;
            }
        }
        $admissionTest->load(['address:id,district_id']);
        $admissionTest->makeHidden(['address_id', 'created_at', 'updated_at']);
        if($request->user()->can('Edit:Admission Test Proctor')) {
            $admissionTest->load('proctors:id,family_name,middle_name,given_name');
            $admissionTest->proctors->append('adorned_name');
            $admissionTest->proctors->makeHidden(['family_name', 'middle_name', 'given_name', 'pivot', 'member']);
        }
        if(
            $request->user()->canAny([
                'View:Admission Test Candidate',
                'Edit:Admission Test Candidate'
            ]) || (
                $admissionTest->in_testing_time_range &&
                $admissionTest->current_user_is_proctor
            )
        ) {
            $admissionTest->load([
                'candidates' => function ($query) use ($admissionTest) {
                    $query->select(
                        array_map(
                            function ($column) use ($query) {
                                return $query->getRelated()->qualifyColumn($column);
                            },
                            [
                                'id', 'family_name', 'middle_name', 'given_name',
                                'birthday', 'passport_type_id', 'passport_number'
                            ]
                        )
                    )->with([
                        'lastAttendedAdmissionTest' => function ($query) use ($admissionTest) {
                            $query->select(
                                array_map(
                                    function ($column) use ($query) {
                                        return $query->getRelated()->qualifyColumn($column);
                                    },
                                    ['id', 'type_id', 'testing_at']
                                )
                            )->with('type:id,interval_month')
                            ->whereNot('test_id', $admissionTest->id);
                        },
                        'passportType:id,name',
                    ]);
                },
            ]);
            $admissionTest->candidates->append([
                'adorned_name', 'has_other_same_passport_user_joined_future_test',
                'has_other_same_passport_user_attended_admission_test',
                'has_same_passport_already_qualification_of_membership',
            ]);
            $admissionTest->candidates->makeHidden(['family_name', 'middle_name', 'given_name', 'passport_type_id', 'member']);
            $pivotHidden = ['test_id', 'user_id', 'order_id'];
            $pivotAppend = [];
            if (
                ! $admissionTest->is_free &&
                $request->user()->canAny([
                    'View:Admission Test Candidate',
                    'Edit:Admission Test Candidate',
                ])
            ) {
                $pivotAppend[] = 'is_free';
            }
            if (
                ! $request->user()->canAny([
                    'View:Admission Test Result',
                    'Edit:Admission Test Result',
                ])
            ) {
                $pivotAppend[] = 'has_result';
                $pivotHidden[] = 'is_pass';
            }
            $admissionTest->candidates->each(
                function ($candidate) use ($pivotHidden, $pivotAppend) {
                    $candidate->passportType->makeHidden(['id']);
                    $candidate->pivot->makeHidden($pivotHidden);
                    if (count($pivotAppend)) {
                        $candidate->pivot->append($pivotAppend);
                    }
                    if ($candidate->lastAttendedAdmissionTest) {
                        $candidate->lastAttendedAdmissionTest
                            ->makeHidden(['id', 'type_id', 'laravel_through_key']);
                        $candidate->lastAttendedAdmissionTest->type
                            ->makeHidden('id');
                    }
                }
            );
        } else if(
            $request->user()->canAny([
                'View:Admission Test Result',
                'Edit:Admission Test Result',
            ])
        ) {
            $admissionTest->load([
                'candidates' => function ($query) {
                    $query->select(
                        array_map(
                            function ($column) use ($query) {
                                return $query->getRelated()->qualifyColumn($column);
                            },
                            ['id', 'birthday']
                        )
                    )->where('is_present', true);
                }
            ]);
            $admissionTest->candidates->makeHidden(['id', 'member']);
            $admissionTest->candidates->each(
                function ($candidate) {
                    $candidate->pivot->makeHidden(['test_id', 'user_id', 'is_present', 'order_id']);
                }
            );
        }

        return Inertia::render('Admin/AdmissionTests/Show')
            ->with('test', $admissionTest)
            ->with(
                'types', AdmissionTestType::orderBy('display_order')
                    ->get(['id', 'name'])
                    ->pluck('name', 'id')
                    ->toArray()
            )->with(
                'locations', Location::distinct()
                    ->has('admissionTests')
                    ->get(['id', 'name'])
                    ->pluck('name', 'id')
                    ->toArray()
            )->with('districts', $districts)
            ->with(
                'addresses', Address::distinct()
                    ->has('admissionTests')
                    ->get(['id', 'value'])
                    ->pluck('value', 'id')
                    ->toArray()
            );
    }

    private function updateLocation(Location $location, string $newLocationName): Location
    {
        $newLocation = $location;
        if ($location->name != $newLocationName) {
            $newLocation = Location::firstWhere([
                'name' => $newLocationName,
            ]);
            if ($location->admissionTests()->count() == 1) {
                if ($newLocation) {
                    $location->delete();
                } else {
                    $location->update([
                        'name' => $newLocationName,
                    ]);
                    $newLocation = $location;
                }
            }
            if (! $newLocation) {
                $newLocation = Location::create([
                    'name' => $newLocationName,
                ]);
            }
        }

        return $newLocation;
    }

    public function update(TestRequest $request, AdmissionTest $admissionTest)
    {
        DB::beginTransaction();
        $from = [
            'testing_date' => $admissionTest->testing_at->format('Y-m-d'),
            'testing_time' => $admissionTest->testing_at->format('H:i:s'),
            'expect_end_time' => $admissionTest->expect_end_at->format('H:i:s'),
            'location' => $admissionTest->location->name,
            'address' => "{$admissionTest->address->value}, {$admissionTest->address->district->name}, {$admissionTest->address->district->area->name}",
        ];
        $address = $admissionTest->address->updateAddress($request->district_id, $request->address);
        $location = $this->updateLocation($admissionTest->location, $request->location);
        $admissionTest->update([
            'type_id' => $request->type_id,
            'testing_at' => $request->testing_at,
            'expect_end_at' => $request->expect_end_at,
            'location_id' => $location->id,
            'address_id' => $address->id,
            'maximum_candidates' => $request->maximum_candidates,
            'is_public' => $request->is_public,
        ]);
        $admissionTest->refresh();
        $to = [
            'testing_date' => $admissionTest->testing_at->format('Y-m-d'),
            'testing_time' => $admissionTest->testing_at->format('H:i:s'),
            'expect_end_time' => $admissionTest->expect_end_at->format('H:i:s'),
            'location' => $admissionTest->location->name,
            'address' => "{$admissionTest->address->value}, {$admissionTest->address->district->name}, {$admissionTest->address->district->area->name}",
        ];
        if (
            $from['testing_date'] != $to['testing_date'] ||
            $from['testing_time'] != $to['testing_time'] ||
            $from['expect_end_time'] != $to['expect_end_time'] ||
            $from['location'] != $to['location'] ||
            $from['address'] != $to['address']
        ) {
            foreach ($admissionTest->candidates as $index => $candidate) {
                $candidate->notify((new UpdateAdmissionTest($from, $to))->delay($index));
            }
        }
        DB::commit();

        return [
            'success' => 'The admission test update success!',
            'type_id' => $admissionTest->type_id,
            'testing_at' => $admissionTest->testing_at->format('Y-m-d H:i:s'),
            'expect_end_at' => $admissionTest->expect_end_at->format('Y-m-d H:i:s'),
            'location_id' => $admissionTest->location_id,
            'location' => $admissionTest->location->name,
            'district_id' => $admissionTest->address->district_id,
            'address_id' => $admissionTest->address_id,
            'address' => $admissionTest->address->value,
            'maximum_candidates' => $admissionTest->maximum_candidates,
            'is_public' => $admissionTest->is_public,
        ];
    }

    public function destroy(AdmissionTest $admissionTest)
    {
        DB::beginTransaction();
        $index = 0;
        $test = clone $admissionTest;
        $candidates = $test->candidates;
        $admissionTest->delete();
        foreach ($candidates as $candidate) {
            if ($test->testing_at > now()) {
                $candidate->notify((new CanceledAdmissionTest($test))->delay($index));
            } else {
                $candidate->notify((new RemovedAdmissionTestRecordByQueue($test, $candidate->pivot))->delay($index));
            }
            $index++;
        }
        DB::commit();

        return ['success' => 'The admission test delete success!'];
    }
}
