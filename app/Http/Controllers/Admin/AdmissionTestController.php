<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTestRequest;
use App\Models\Address;
use App\Models\AdmissionTest;
use App\Models\Area;
use App\Models\Location;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class AdmissionTestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:Edit:Admission Test')];
    }

    public function index()
    {
        return view('admin.admission-tests.index')
            ->with(
                'tests', AdmissionTest::sortable('testing_at')
                    ->paginate()
            );
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

    public function store(AdmissionTestRequest $request)
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
            'location_id' => $location->id,
            'address_id' => $address->id,
            'maximum_candidates' => $request->maximum_candidates,
            'is_public' => $request->is_public,
        ]);
        DB::commit();

        return redirect()->route(
            'admin.admission-tests.show',
            ['admission_test' => $test]);
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

    public function update(AdmissionTestRequest $request, AdmissionTest $admissionTest)
    {
        DB::beginTransaction();
        $address = $this->updateAddress($admissionTest->address, $request->address, $request->district_id);
        $location = $this->updateLocation($admissionTest->location, $request->location);
        $admissionTest->update([
            'testing_at' => $request->testing_at,
            'location_id' => $location->id,
            'address_id' => $address->id,
            'maximum_candidates' => $request->maximum_candidates,
            'is_public' => $request->is_public,
        ]);
        $admissionTest->refresh();
        DB::commit();

        return [
            'success' => 'The admission test update success!',
            'testing_at' => $admissionTest->testing_at,
            'location' => $admissionTest->location->name,
            'district_id' => $admissionTest->address->district_id,
            'address' => $admissionTest->address->address,
            'maximum_candidates' => $admissionTest->maximum_candidates,
            'is_public' => $admissionTest->is_public,
        ];
    }
}
