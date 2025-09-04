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
                        $request->user()->can('Edit:Admission Test')
                    ) {
                        return $next($request);
                    }
                    abort(403);
                }
            ))->only('index'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $test = $request->route('admission_test');
                    if (
                        $request->user()->can('Edit:Admission Test') || (
                            $test->inTestingTimeRange &&
                            in_array($request->user()->id, $test->proctors->pluck('id')->toArray())
                        )
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
        $tests->append('in_testing_time_range');
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
                    ->get('address')
                    ->pluck('address')
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
            'address' => $request->address,
        ]);
        $test = AdmissionTest::create([
            'type_id' => $request->type_id,
            'testing_at' => $request->testing_at,
            'expect_end_at' => $request->expect_end_at,
            'location_id' => $location->id,
            'address_id' => $address->id,
            'maximum_candidates' => $request->maximum_candidates,
            'is_public' => $request->is_public,
        ]);
        DB::commit();

        return redirect()->route(
            'admin.admission-tests.show',
            ['admission_test' => $test]
        );
    }

    public function show(AdmissionTest $admissionTest)
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
        $admissionTest->load([
            'proctors', 'address' => function ($query) {
                $query->select(['id', 'district_id']);
            }, 'candidates' => function ($query) use ($admissionTest) {
                $query->with([
                    'lastAttendedAdmissionTest' => function ($query) use ($admissionTest) {
                        $query->with([
                            'type' => function ($query) {
                                $query->select(['id', 'interval_month']);
                            },
                        ])->whereNot('test_id', $admissionTest->id);
                    }, 'passportType' => function ($query) {
                        $query->select(['id', 'name']);
                    },
                ]);
            },
        ]);
        $admissionTest->makeHidden(['address_id', 'created_at', 'updated_at']);
        $admissionTest->proctors->append('adorned_name');
        $admissionTest->proctors->makeHidden([
            'username', 'member', 'family_name', 'middle_name', 'given_name',
            'passport_type_id', 'passport_number', 'birthday', 'gender_id',
            'synced_to_stripe', 'created_at', 'updated_at', 'pivot',
        ]);
        $admissionTest->candidates->append([
            'adorned_name', 'has_other_same_passport_user_joined_future_test',
            'last_attended_admission_test_of_other_same_passport_user',
            'has_same_passport_already_qualification_of_membership',
            'last_attended_admission_test',
        ]);
        $admissionTest->candidates->makeHidden([
            'username', 'member', 'family_name', 'middle_name', 'given_name',
            'birthday', 'gender_id', 'synced_to_stripe', 'created_at', 'updated_at',
        ]);
        foreach ($admissionTest->candidates as $candidate) {
            $candidate->passportType->makeHidden('id');
            if ($candidate->lastAttendedAdmissionTest) {
                $candidate->lastAttendedAdmissionTest->makeHidden([
                    'id', 'type_id', 'expect_end_at', 'address_id', 'location_id',
                    'maximum_candidates', 'is_public', 'created_at', 'updated_at',
                    'laravel_through_key',
                ]);
                $candidate->lastAttendedAdmissionTest->type->makeHidden('id');
            }
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
                    ->get(['id', 'address'])
                    ->pluck('address', 'id')
                    ->toArray()
            );
    }

    private function updateAddress(Address $address, string $newAddress, int $newDistrictID)
    {
        $addressModel = $address;
        if (
            $newAddress != $address->address ||
            $newDistrictID != $address->district_id
        ) {
            $addressModel = Address::firstWhere([
                'district_id' => $newDistrictID,
                'address' => $newAddress,
            ]);
            if ($address->admissionTests()->count() == 1) {
                if ($addressModel) {
                    $address->delete();
                } else {
                    $address->update([
                        'district_id' => $newDistrictID,
                        'address' => $newAddress,
                    ]);
                    $addressModel = $address;
                }
            }
            if (! $addressModel) {
                $addressModel = Address::create([
                    'district_id' => $newDistrictID,
                    'address' => $newAddress,
                ]);
            }
        }

        return $addressModel;
    }

    private function updateLocation(Location $location, string $newLocationName)
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
            'address' => "{$admissionTest->address->address}, {$admissionTest->address->district->name}, {$admissionTest->address->district->area->name}",
        ];
        $address = $this->updateAddress($admissionTest->address, $request->address, $request->district_id);
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
            'address' => "{$admissionTest->address->address}, {$admissionTest->address->district->name}, {$admissionTest->address->district->area->name}",
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
            'address' => $admissionTest->address->address,
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
