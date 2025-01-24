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
        return [(new Middleware('permission:Edit:Admission Test'))];
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
        $address = Address::firstOrCreate([
            'district_id' => $request->district_id,
            'address' => $request->address,
        ]);
        $location = Location::firstOrCreate([
            'address_id' => $address->id,
            'name' => $request->location,
        ]);
        $test = AdmissionTest::create([
            'testing_at' => $request->testing_at,
            'location_id' => $location->id,
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
        return view('admin.admission-tests.show')
            ->with('test', $admissionTest);
    }

    public function update(AdmissionTestRequest $request, AdmissionTest $admissionTest)
    {
        DB::beginTransaction();
        $address = $admissionTest->location->address;
        $newAddress = $address;
        $location = $admissionTest->location;
        $newLocation = $location;
        if(
            $request->address != $address->address ||
            $request->district_id != $address->district_id
        ) {
            $newAddress = Address::firstWhere([
                'district_id' => $request->district_id,
                'address' => $request->address,
            ]);
            if(
                count($address->locations) == 1 &&
                count($location->admissionTests) == 1
            ) {
                if($newAddress) {
                    $address->delete();
                } else {
                    $address->update([
                        'district_id' => $request->district_id,
                        'address' => $request->address,
                    ]);
                    $newAddress = $address;
                }
            }
        }
        if(!$newAddress) {
            $newAddress = Address::create([
                'district_id' => $request->district_id,
                'address' => $request->address,
            ]);
        }
        if(
            $address->id != $newAddress->id ||
            $location->name != $request->location
        ) {
            $newLocation = Location::firstWhere([
                'name' => $request->location,
                'address_id' => $newAddress->id,
            ]);
            if(count($location->admissionTests) == 1) {
                if($newLocation) {
                    $location->delete();
                } else {
                    $location->update([
                        'name' => $request->location,
                        'address_id' => $newAddress->id,
                    ]);
                    $newLocation = $location;
                }
            }
        }
        if(!$newLocation) {
            $newLocation = Location::create([
                'name' => $request->location,
                'address_id' => $newAddress->id,
            ]);
        }
        $admissionTest->update([
            'testing_at' => $request->testing_at,
            'location_id' => $newLocation->id,
            'maximum_candidates' => $request->maximum_candidates,
            'is_public' => $request->is_public,
        ]);
        $admissionTest->refresh();
        DB::commit();

        return [
            'success' => 'The admission test update success!',
            'testing_at' => $admissionTest->testing_at,
            'location' => $admissionTest->location->name,
            'district_id' => $admissionTest->location->address->district_id,
            'address' => $admissionTest->location->address->address,
            'maximum_candidates' => $admissionTest->maximum_candidates,
            'is_public' => $admissionTest->is_public,
        ];
    }
}
