<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\Admin\AdmissionTest\TestRequest;
use App\Models\Address;
use App\Models\AdmissionTest;
use App\Models\Area;
use App\Models\Location;
use App\Notifications\UpdateAdmissionTest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
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
                            $test->inTestingTimeRange() &&
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
        $tests = $tests->sortable('testing_at')->paginate();

        return view('admin.admission-tests.index')
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

        return view('admin.admission-tests.create')
            ->with(
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

        return view('admin.admission-tests.show')
            ->with('test', $admissionTest)
            ->with(
                'locations', Location::distinct()
                    ->has('admissionTests')
                    ->get('name')
                    ->pluck('name')
                    ->toArray()
            )->with('districts', $districts)
            ->with(
                'addresses', Address::distinct()
                    ->has('admissionTests')
                    ->get('address')
                    ->pluck('address')
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
            'testing_time' => $admissionTest->testing_at->format('H:i'),
            'expect_end_time' => $admissionTest->expect_end_at->format('H:i'),
            'location' => $admissionTest->location->name,
            'address' => "{$admissionTest->address->address}, {$admissionTest->address->district->name}, {$admissionTest->address->district->area->name}",
        ];
        $address = $this->updateAddress($admissionTest->address, $request->address, $request->district_id);
        $location = $this->updateLocation($admissionTest->location, $request->location);
        $admissionTest->update([
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
            'testing_time' => $admissionTest->testing_at->format('H:i'),
            'expect_end_time' => $admissionTest->expect_end_at->format('H:i'),
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
            'testing_at' => $admissionTest->testing_at->format('Y-m-d H:i'),
            'expect_end_at' => $admissionTest->expect_end_at->format('Y-m-d H:i'),
            'location' => $admissionTest->location->name,
            'district_id' => $admissionTest->address->district_id,
            'address' => $admissionTest->address->address,
            'maximum_candidates' => $admissionTest->maximum_candidates,
            'is_public' => $admissionTest->is_public,
        ];
    }
}
